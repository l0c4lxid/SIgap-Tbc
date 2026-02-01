<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor | SITUBA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'JetBrains Mono', monospace;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .monitor-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
        }
        .progress-bar {
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .blink {
            animation: blink-animation 1s steps(5, start) infinite;
        }
        @keyframes blink-animation {
            to { visibility: hidden; }
        }
        .scan-line {
            width: 100%;
            height: 100px;
            z-index: 10;
            background: linear-gradient(0deg, rgba(0,0,0,0) 0%, rgba(51, 255, 51, 0.1) 50%, rgba(0,0,0,0) 100%);
            opacity: 0.1;
            position: absolute;
            bottom: 100%;
            animation: scanline 10s linear infinite;
            pointer-events: none;
        }
        @keyframes scanline {
            0% { bottom: 100%; }
            100% { bottom: -100%; }
        }
        /* Chart Container adjustments */
        .chart-container {
            position: relative;
            height: 150px;
            width: 100%;
        }
    </style>
</head>
<body class="h-screen w-full overflow-hidden flex flex-col relative">

    <div class="scan-line"></div>

    @if($isLocked)
        <!-- LOCK SCREEN -->
        <div class="flex-1 flex flex-col items-center justify-center p-4">
            <div class="monitor-card rounded-lg p-8 w-full max-w-md shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
                <div class="mb-6 text-center">
                    <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h1 class="text-2xl font-bold text-red-500 tracking-wider">SYSTEM LOCKED</h1>
                    <p class="text-gray-400 text-xs mt-2">SECURE ACCESS REQUIRED</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 bg-red-900/50 border border-red-700 text-red-200 px-4 py-2 rounded text-xs">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('system.monitor.view') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="monitor_key" class="block text-xs text-gray-400 mb-1 uppercase">Access Key</label>
                        <input type="password" name="monitor_key" id="monitor_key" 
                            class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white focus:outline-none focus:border-red-500 transition-colors font-mono"
                            placeholder="Enter key..." autofocus>
                    </div>
                    <button type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors uppercase tracking-widest text-sm">
                        Authenticate
                    </button>
                    <a href="/" class="block text-center text-xs text-gray-600 hover:text-gray-400 mt-4">
                        &larr; Return to Dashboard
                    </a>
                </form>
            </div>
        </div>
    @else
        <!-- MONITOR DASHBOARD -->
        <header class="bg-slate-900 border-b border-slate-700 p-4 flex justify-between items-center z-10 shadow-lg">
            <div class="flex items-center space-x-3">
                 <div class="w-3 h-3 rounded-full bg-green-500 blink"></div>
                 <h1 class="text-xl font-bold text-green-500 tracking-wider">SITUBA<span class="text-white">_MONITOR</span> <span class="text-xs text-gray-500 font-normal ml-2 hidden sm:inline">[SHARED_HOSTING_MODE]</span></h1>
            </div>
            <div class="flex items-center space-x-6 text-xs text-gray-400">
                <div class="flex items-center hidden sm:flex">
                     <span class="mr-2 text-gray-600">IP:</span>
                     <span id="server-ip" class="text-blue-400">Loading...</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2">STATUS:</span>
                    <span id="connection-status" class="text-green-400">CONNECTING...</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2">UPDATED:</span>
                    <span id="last-updated" class="text-white font-mono">--:--:--</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 overflow-y-auto z-10 pb-20">
            
            <!-- CPU -->
            <div class="monitor-card rounded-lg p-6 relative group hover:border-blue-500 transition-colors col-span-1 lg:col-span-2">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-blue-400 text-sm font-bold uppercase tracking-widest">CPU Load</h3>
                        <p class="text-xs text-gray-600">Processing Activity (Last 60s)</p>
                    </div>
                    <div class="text-right">
                        <span id="cpu-value" class="text-3xl font-bold text-white">--</span>
                        <span class="text-sm text-gray-500">%</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="cpuChart"></canvas>
                </div>
            </div>

            <!-- Memory -->
            <div class="monitor-card rounded-lg p-6 relative group hover:border-purple-500 transition-colors col-span-1 lg:col-span-2">
                <div class="flex justify-between items-start mb-4">
                     <div>
                        <h3 class="text-purple-400 text-sm font-bold uppercase tracking-widest">Memory Usage</h3>
                         <p class="text-xs text-gray-600" id="memory-type-label">Physical RAM</p>
                    </div>
                    <div class="text-right">
                        <span id="memory-value" class="text-3xl font-bold text-white">--</span>
                        <p class="text-xs text-gray-500" id="memory-detail">-- / --</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="memoryChart"></canvas>
                </div>
            </div>

            <!-- Disk -->
            <div class="monitor-card rounded-lg p-6 relative group hover:border-amber-500 transition-colors">
                <h3 class="text-amber-400 text-sm font-bold uppercase tracking-widest mb-4">Storage</h3>
                <div class="flex items-end mb-4">
                    <span id="disk-value" class="text-4xl font-bold text-white">--</span>
                </div>
                <div class="w-full bg-slate-800 h-2 rounded-full mb-2 overflow-hidden">
                    <div id="disk-bar" class="bg-amber-500 h-full w-0 progress-bar"></div>
                </div>
                <p class="text-xs text-gray-500 mb-1" id="disk-detail">-- / --</p>
                <p class="text-[10px] text-gray-600">Path: <span id="server-path">...</span></p>
            </div>

            <!-- Database & Server Info -->
            <div class="monitor-card rounded-lg p-6 relative group hover:border-green-500 transition-colors">
                <h3 class="text-green-400 text-sm font-bold uppercase tracking-widest mb-4">Infrastructure</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2">
                        <span class="text-xs text-gray-500">Database</span>
                        <div class="flex items-center">
                            <div id="db-indicator-dot" class="w-2 h-2 rounded-full bg-gray-600 mr-2"></div>
                            <span id="db-status" class="text-xs font-bold text-gray-400">Check</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2">
                        <span class="text-xs text-gray-500">DB Ver</span>
                        <span id="db-version" class="text-xs text-white">--</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2">
                        <span class="text-xs text-gray-500">PHP Ver</span>
                        <span id="php-version" class="text-xs text-white">--</span>
                    </div>
                     <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">Laravel</span>
                        <span id="laravel-version" class="text-xs text-white">--</span>
                    </div>
                </div>
            </div>
            
            <!-- Terminal Log -->
            <div class="monitor-card rounded-lg p-4 col-span-1 md:col-span-2 lg:col-span-4 h-40 font-mono text-xs text-green-400 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-6 bg-slate-800/90 px-2 flex items-center border-b border-slate-700 justify-between">
                    <span class="text-gray-400">root@situba:~# log_stream</span>
                    <span class="text-[10px] text-gray-600" id="server-software">Server: ...</span>
                </div>
                <div id="log-container" class="mt-8 space-y-1 opacity-80 h-full overflow-y-auto scrollbar-hide">
                    <p>> Initializing monitor protocol...</p>
                </div>
            </div>

        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const apiKey = @json($apiKey);
                if (!apiKey) return;

                const apiUrl = "{{ route('api.system-monitor') }}?key=" + apiKey;
                const logContainer = document.getElementById('log-container');
                
                // Chart Configuration
                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animation for realtime feel
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#334155' },
                            ticks: { color: '#94a3b8', font: { size: 10 } }
                        }
                    },
                    elements: {
                        point: { radius: 0, hitRadius: 10 },
                        line: { tension: 0.4, borderWidth: 2 }
                    }
                };

                // CPU Chart
                const cpuCtx = document.getElementById('cpuChart').getContext('2d');
                const cpuChart = new Chart(cpuCtx, {
                    type: 'line',
                    data: {
                        labels: Array(20).fill(''),
                        datasets: [{
                            data: Array(20).fill(0),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            ...commonOptions.scales,
                            y: { ...commonOptions.scales.y, max: 100 }
                        }
                    }
                });

                // Memory Chart
                const memCtx = document.getElementById('memoryChart').getContext('2d');
                const memChart = new Chart(memCtx, {
                    type: 'line',
                    data: {
                        labels: Array(20).fill(''),
                        datasets: [{
                            data: Array(20).fill(0),
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            fill: true
                        }]
                    },
                    options: commonOptions
                });

                const log = (msg) => {
                    const p = document.createElement('p');
                    const time = new Date().toLocaleTimeString('en-US', {hour12: false});
                    p.innerHTML = `<span class="text-gray-500">[${time}]</span> ${msg}`;
                    logContainer.prepend(p);
                    if (logContainer.children.length > 20) logContainer.lastChild.remove();
                };

                const fetchStats = async () => {
                    try {
                        const response = await fetch(apiUrl);
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        
                        updateUI(data);
                        document.getElementById('connection-status').textContent = "ONLINE";
                        document.getElementById('connection-status').classList.remove('text-red-500');
                        document.getElementById('connection-status').classList.add('text-green-400');

                    } catch (error) {
                        console.error('Fetch error:', error);
                        document.getElementById('connection-status').textContent = "OFFLINE";
                        document.getElementById('connection-status').classList.add('text-red-500');
                        document.getElementById('connection-status').classList.remove('text-green-400');
                        log("<span class='text-red-500'>Connection lost...</span>");
                    }
                };

                const updateUI = (data) => {
                    const sys = data.system;
                    const srv = data.server;
                    const db = data.database;

                    // Server Info
                    document.getElementById('server-ip').textContent = srv.ip;
                    document.getElementById('server-software').textContent = srv.software;
                    document.getElementById('php-version').textContent = srv.php_version;
                    document.getElementById('laravel-version').textContent = srv.laravel_version;
                    
                    // Time
                    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();

                    // CPU
                    const cpuLoad = sys.cpu_load;
                    document.getElementById('cpu-value').textContent = cpuLoad;
                    
                    // Update CPU Chart
                    const cpuData = cpuChart.data.datasets[0].data;
                    cpuData.shift();
                    cpuData.push(cpuLoad);
                    cpuChart.update();

                    if(cpuLoad > 80) log("<span class='text-amber-500'>High CPU Load: " + cpuLoad + "%</span>");

                    // Memory
                    const memUsedRaw = sys.memory.raw.used; // Bytes
                    const memTotalRaw = sys.memory.raw.total; // Bytes
                    const memUsedMB = (memUsedRaw / 1024 / 1024).toFixed(1);
                    const memTotalMB = (memTotalRaw > 0) ? (memTotalRaw / 1024 / 1024).toFixed(1) : '???';
                    
                    document.getElementById('memory-value').textContent = sys.memory.used;
                    document.getElementById('memory-detail').textContent = `/ ${sys.memory.total}`;
                    
                    if (sys.memory.raw.simulated) {
                        document.getElementById('memory-type-label').textContent = "Process Limit (Shared)";
                    } else {
                        document.getElementById('memory-type-label').textContent = "Physical RAM";
                    }

                    // Update Memory Chart (in MB)
                    const memData = memChart.data.datasets[0].data;
                    memData.shift();
                    memData.push(memUsedMB);
                    memChart.update();

                    // Disk
                    const diskPercent = parseFloat(sys.disk.usage_percentage);
                    document.getElementById('disk-value').textContent = sys.disk.usage_percentage;
                    document.getElementById('disk-detail').textContent = `${sys.disk.used} used of ${sys.disk.total}`;
                    document.getElementById('disk-bar').style.width = diskPercent + '%';
                    
                    // DB
                    const dbEl = document.getElementById('db-status');
                    const dbDot = document.getElementById('db-indicator-dot');
                    
                    if (db.status === 'connected') {
                        dbEl.textContent = 'CONNECTED';
                        dbEl.classList.remove('text-red-500');
                        dbEl.classList.add('text-green-500');
                        dbDot.className = 'w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_10px_#22c55e]';
                        document.getElementById('db-version').textContent = db.version || 'Unknown';
                    } else {
                        dbEl.textContent = 'ERROR';
                        dbEl.classList.add('text-red-500');
                        dbDot.className = 'w-2 h-2 rounded-full bg-red-500 mr-2 shadow-[0_0_10px_#ef4444]';
                        document.getElementById('db-version').textContent = 'Error';
                        log("<span class='text-red-500'>DB Error: " + db.error + "</span>");
                    }
                };

                // Initial fetch
                fetchStats();
                log("System monitor initialized.");

                // Poll every 2 seconds
                setInterval(fetchStats, 2000);
            });
        </script>
    @endif
</body>
</html>
