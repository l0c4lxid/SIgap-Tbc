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
        .chart-container {
            position: relative;
            height: 120px;
            width: 100%;
        }
        /* Mobile Adjustments */
        @media (max-width: 640px) {
            .chart-container {
                height: 100px;
            }
            .monitor-card {
                padding: 1rem;
            }
            main {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="h-screen w-full overflow-hidden flex flex-col relative">

    <div class="scan-line"></div>

    @if($isLocked)
        <!-- LOCK SCREEN -->
        <div class="flex-1 flex flex-col items-center justify-center p-4">
            <div class="monitor-card rounded-lg p-6 w-full max-w-sm shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
                <div class="mb-6 text-center">
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h1 class="text-xl font-bold text-red-500 tracking-wider">SYSTEM LOCKED</h1>
                    <p class="text-gray-400 text-[10px] mt-2">SECURE ACCESS REQUIRED</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 bg-red-900/50 border border-red-700 text-red-200 px-3 py-2 rounded text-[10px]">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('system.monitor.view') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="monitor_key" class="block text-[10px] text-gray-400 mb-1 uppercase">Access Key</label>
                        <input type="password" name="monitor_key" id="monitor_key" 
                            class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white focus:outline-none focus:border-red-500 transition-colors font-mono text-sm"
                            placeholder="Enter key..." autofocus>
                    </div>
                    <button type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors uppercase tracking-widest text-xs">
                        Authenticate
                    </button>
                    <a href="/" class="block text-center text-[10px] text-gray-600 hover:text-gray-400 mt-4">
                        &larr; Return to Dashboard
                    </a>
                </form>
            </div>
        </div>
    @else
        <!-- MONITOR DASHBOARD -->
        <header class="bg-slate-900 border-b border-slate-700 p-4 flex justify-between items-center z-10 shadow-lg">
            <div class="flex items-center space-x-2 sm:space-x-3">
                 <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-green-500 blink"></div>
                 <h1 class="text-lg sm:text-xl font-bold text-green-500 tracking-wider">SITUBA<span class="text-white">_MON</span> <span class="text-[10px] text-gray-500 font-normal ml-1 hidden md:inline">[SHARED_HOSTING]</span></h1>
            </div>
            <div class="flex items-center space-x-3 sm:space-x-6 text-[10px] sm:text-xs text-gray-400">
                <div class="flex items-center hidden lg:flex">
                     <span class="mr-2 text-gray-600">PATH:</span>
                     <span id="server-path-header" class="text-blue-400 truncate max-w-[150px]">...</span>
                </div>
                <!-- IP showed on Desktop only now, to save space -->
                <div class="flex items-center hidden md:flex">
                     <span class="mr-2 text-gray-600">IP:</span>
                     <span id="server-ip" class="text-blue-400">Loading...</span>
                </div>
                <div class="flex items-center">
                    <!-- Text hidden on mobile, simplified dot -->
                    <span class="mr-2 hidden sm:inline">STATUS:</span>
                    <span id="connection-status" class="text-green-400">ON</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 overflow-y-auto z-10 pb-20">
            
            <!-- CPU (Graph) -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-blue-500 transition-colors col-span-1 md:col-span-2">
                <div class="flex justify-between items-start mb-2 sm:mb-4">
                    <div>
                         <h3 class="text-blue-400 text-xs sm:text-sm font-bold uppercase tracking-widest">CPU Load</h3>
                         <p class="text-[10px] text-gray-600 hidden sm:block">Processing Activity</p>
                    </div>
                    <div class="text-right">
                        <span id="cpu-value" class="text-2xl sm:text-3xl font-bold text-white">--</span>
                        <span class="text-[10px] sm:text-sm text-gray-500">%</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="cpuChart"></canvas>
                </div>
            </div>

            <!-- Memory (Graph) -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-purple-500 transition-colors col-span-1 md:col-span-2">
                <div class="flex justify-between items-start mb-2 sm:mb-4">
                     <div>
                        <h3 class="text-purple-400 text-xs sm:text-sm font-bold uppercase tracking-widest">Memory</h3>
                         <p class="text-[10px] text-gray-600 hidden sm:block" id="memory-type-label">RAM</p>
                    </div>
                    <div class="text-right">
                        <span id="memory-value" class="text-2xl sm:text-3xl font-bold text-white">--</span>
                        <p class="text-[10px] sm:text-xs text-gray-500" id="memory-detail">-- / --</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="memoryChart"></canvas>
                </div>
            </div>

            <!-- Disk -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-amber-500 transition-colors">
                <h3 class="text-amber-400 text-xs sm:text-sm font-bold uppercase tracking-widest mb-2 sm:mb-4">Storage</h3>
                <div class="flex items-end mb-2 sm:mb-4">
                    <span id="disk-value" class="text-3xl sm:text-4xl font-bold text-white">--</span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 sm:h-2 rounded-full mb-2 overflow-hidden">
                    <div id="disk-bar" class="bg-amber-500 h-full w-0 progress-bar"></div>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500 mb-1" id="disk-detail">-- / --</p>
                <div class="mt-2 pt-2 border-t border-slate-800">
                    <p class="text-[10px] text-amber-300 font-mono truncate" id="server-path">...</p>
                </div>
            </div>

            <!-- Database Size -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-indigo-500 transition-colors">
                <h3 class="text-indigo-400 text-xs sm:text-sm font-bold uppercase tracking-widest mb-2 sm:mb-4">DB Size</h3>
                <div class="flex items-end mb-2 sm:mb-4">
                    <span id="db-size" class="text-3xl sm:text-4xl font-bold text-white">--</span>
                    <span class="text-[10px] sm:text-sm text-gray-500 mb-2 ml-1">MB</span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 sm:h-2 rounded-full mb-2 overflow-hidden">
                    <div id="db-size-bar" class="bg-indigo-500 h-full w-0 progress-bar"></div>
                </div>
                 <div class="flex justify-between items-center mt-2">
                    <span class="text-[10px] text-gray-500">Status</span>
                    <div class="flex items-center">
                        <div id="db-indicator-dot" class="w-2 h-2 rounded-full bg-gray-600 mr-2"></div>
                        <span id="db-status" class="text-[10px] sm:text-xs font-bold text-gray-400">Check</span>
                    </div>
                </div>
            </div>

            <!-- Log File Size -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-pink-500 transition-colors">
                <div class="flex justify-between items-start mb-2">
                     <h3 class="text-pink-400 text-xs sm:text-sm font-bold uppercase tracking-widest">Logs</h3>
                     <span class="text-[10px] text-gray-600">laravel.log</span>
                </div>
                
                <div class="flex items-end mb-2 sm:mb-4">
                    <span id="log-size" class="text-3xl sm:text-4xl font-bold text-white">--</span>
                </div>
                 
                 <div class="mt-2 pt-2 border-t border-slate-800">
                     <div class="flex items-center text-[10px] sm:text-xs text-gray-400">
                        @csrf
                         <span class="w-2 h-2 rounded-full bg-green-500 mr-2" id="log-status-dot"></span>
                         <span id="log-status-text">File Active</span>
                     </div>
                </div>
            </div>

            <!-- Infrastructure Info -->
            <div class="monitor-card rounded-lg p-4 sm:p-6 relative group hover:border-green-500 transition-colors">
                <h3 class="text-green-400 text-xs sm:text-sm font-bold uppercase tracking-widest mb-2 sm:mb-4">Info</h3>
                <div class="space-y-2 sm:space-y-3">
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500">Software</span>
                         <span id="server-software" class="text-[10px] text-white truncate max-w-[120px]">--</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2">
                        <span class="text-[10px] text-gray-500">PHP</span>
                        <span id="php-version" class="text-[10px] text-white">--</span>
                    </div>
                     <div class="flex justify-between items-center">
                        <span class="text-[10px] text-gray-500">Laravel</span>
                        <span id="laravel-version" class="text-[10px] text-white">--</span>
                    </div>
                </div>
            </div>
            
            <!-- Terminal Log -->
            <div class="monitor-card rounded-lg p-3 sm:p-4 col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4 h-32 sm:h-40 font-mono text-[10px] sm:text-xs text-green-400 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-6 bg-slate-800/90 px-2 flex items-center border-b border-slate-700 justify-between">
                    <span class="text-gray-400">root@situba:~# log_stream</span>
                    <span class="text-[10px] text-gray-500" id="last-updated">--:--:--</span>
                </div>
                <div id="log-container" class="mt-6 sm:mt-8 space-y-1 opacity-80 h-full overflow-y-auto scrollbar-hide">
                    <p>> Initializing metrics...</p>
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
                    animation: false, 
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
                        document.getElementById('connection-status').textContent = "ON";
                        document.getElementById('connection-status').classList.remove('text-red-500');
                        document.getElementById('connection-status').classList.add('text-green-400');

                    } catch (error) {
                        console.error('Fetch error:', error);
                        document.getElementById('connection-status').textContent = "OFF";
                        document.getElementById('connection-status').classList.add('text-red-500');
                        document.getElementById('connection-status').classList.remove('text-green-400');
                        log("<span class='text-red-500'>Connection lost...</span>");
                    }
                };

                const updateUI = (data) => {
                    const sys = data.system;
                    const srv = data.server;
                    const db = data.database;
                    const logInfo = data.log;

                    // Show error if present
                    if (data.error) {
                        log("<span class='text-red-500'>" + data.error + "</span>");
                        document.getElementById('connection-status').textContent = "ERROR";
                        document.getElementById('connection-status').classList.add('text-red-500');
                        document.getElementById('connection-status').classList.remove('text-green-400');
                    } else {
                         // Reset status if OK
                    }

                    // Server Info (Check existence before setting content for mobile hidden elements)
                    if(document.getElementById('server-ip')) document.getElementById('server-ip').textContent = srv.ip;
            // cPanel/LVE Data Rendering
            if (srv.is_cpanel && data.cpanel && data.cpanel.length > 0) {
                // Clear existing grid to render cPanel specific items
                const grid = document.querySelector('.grid');
                
                // Keep the Infrastructure and Log cards, replace others or append
                // Strategy: We will render a new "cPanel Resource Limits" section at top
                
                // Helper to create card HTML
                const createCard = (title, value, percent, icon) => {
                     let colorClass = 'text-emerald-400';
                     if (percent > 80) colorClass = 'text-red-500';
                     else if (percent > 60) colorClass = 'text-yellow-400';
                     
                     return `
                        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-lg">
                            <h3 class="text-slate-400 text-xs font-mono uppercase tracking-widest mb-2">${title}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1 font-mono tracking-tighter">${value}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">Usage Limit</div>
                                </div>
                                <div class="text-right">
                                    <div class="${colorClass} text-xl font-bold font-mono">${percent}%</div>
                                    <div class="h-1 w-16 bg-slate-700 rounded-full mt-2 overflow-hidden">
                                        <div class="h-full ${colorClass.replace('text', 'bg')}" style="width: ${percent}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     `;
                };

                let cpanelHtml = '';
                
                // We want to render ALL metrics returned by cPanel StatsBar
                
                const stats = data.cpanel;
                
                // Helper to format title from snake_case key if needed
                const formatTitle = (str) => {
                    return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                };

                // Sort stats to put "Resources" (CPU, Mem) first if desired, or just raw
                // We'll trust the API order or sort specifically if needed. 
                // Let's render everything available.

                stats.forEach(item => {
                    // Try to determine Label
                    // UAPI often provides 'item' (e.g. 'bandwidth_usage') or 'name' ('Bandwidth')
                    // For StatsBar, 'item' is usually the internal key, 'count' the value string.
                    
                    // We prefer the 'name' or 'description' if available, else format the key.
                    let label = item.name || item.item || item.id;
                    if (label) label = formatTitle(label.replace('_usage', ''));
                    
                    // Use the pre-formatted string from cPanel if available (e.g. "60.59 MB / 1 GB (5.92%)")
                    // Often found in 'count' (if it's a string) or 'format'
                    let val = item.format || item.count;
                    
                    // Percent for bar
                    // If percent is missing but we have count/max, we could calc, but cPanel usually gives `percent`
                    // Some items like "Subdomains 0 / Infinity" have percent: 0
                    let pct = item.percent || 0;
                    
                    // Handle "infinity" or 0 usage
                    if (item.max === 'unlimited' || item.max === 0 || item.max === '∞') {
                         pct = 0; // Don't show full bar for infinity
                    }
                    
                    // Clean up display value if it's overly verbose, 
                    // but user requested "Databases 3 / 10 (30%)", so we keep the detailed string.
                    // If 'val' is just the number "3", we might want to construct "3 / 10".
                    if (item.max && item.max !== '0' && typeof val !== 'string') {
                         val = `${item.count} / ${item.max}`;
                    }

                    // Render
                    cpanelHtml += createCard(label, val, pct, '');
                });

                // If we generated cPanel cards, inject them
                if (cpanelHtml) {
                    const existingCpanelContainer = document.getElementById('cpanel-stats');
                    if (existingCpanelContainer) {
                         existingCpanelContainer.innerHTML = cpanelHtml;
                    } else {
                        // Create container if not exists (insert after graphs)
                        const container = document.createElement('div');
                        container.id = 'cpanel-stats';
                        container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6';
                        container.innerHTML = cpanelHtml;
                        
                        // Find a good place to insert. Maybe replace the existing generic cards?
                        // Let's hide generic usage cards if cPanel is active
                        document.getElementById('generic-usage-cards').style.display = 'none';
                        
                        // Insert after charts
                        const charts = document.getElementById('charts-section');
                        charts.parentNode.insertBefore(container, charts.nextSibling);
                    }
                }
            } else {
                 // Standard Mode
                 if(document.getElementById('generic-usage-cards')) document.getElementById('generic-usage-cards').style.display = 'grid';
                 document.getElementById('cpu-value').textContent = sys.cpu_load; // Corrected from cpu-val
                 document.getElementById('memory-value').textContent = sys.memory.used; // Corrected from mem-val
                 document.getElementById('memory-detail').textContent = `/ ${sys.memory.total}`; // Corrected from mem-total
                 document.getElementById('disk-value').textContent = sys.disk.usage_percentage; // Corrected from disk-val
                 document.getElementById('disk-detail').textContent = `${sys.disk.used} / ${sys.disk.total}`; // Corrected from disk-total
            }

                    document.getElementById('server-software').textContent = srv.software;
                    document.getElementById('php-version').textContent = srv.php_version;
                    document.getElementById('laravel-version').textContent = srv.laravel_version;
                    document.getElementById('server-path').textContent = srv.path;
                    if(document.getElementById('server-path-header')) document.getElementById('server-path-header').textContent = srv.path;
                    
                    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();

                    // CPU
                    const cpuLoad = sys.cpu_load;
                    document.getElementById('cpu-value').textContent = cpuLoad;
                    const cpuData = cpuChart.data.datasets[0].data;
                    cpuData.shift();
                    cpuData.push(cpuLoad);
                    cpuChart.update();

                    if(cpuLoad > 85) log("<span class='text-amber-500'>High CPU: " + cpuLoad + "%</span>");

                    // Memory
                    const memUsedRaw = sys.memory.raw.used; 
                    const memTotalRaw = sys.memory.raw.total; 
                    const memUsedMB = (memUsedRaw / 1024 / 1024).toFixed(1);
                    
                    document.getElementById('memory-value').textContent = sys.memory.used;
                    document.getElementById('memory-detail').textContent = `/ ${sys.memory.total}`;
                    
                    if (sys.memory.raw.simulated) {
                        if(document.getElementById('memory-type-label')) document.getElementById('memory-type-label').textContent = "Process Limit";
                    } else {
                        if(document.getElementById('memory-type-label')) document.getElementById('memory-type-label').textContent = "RAM";
                    }

                    const memData = memChart.data.datasets[0].data;
                    memData.shift();
                    memData.push(memUsedMB);
                    memChart.update();

                    // Disk
                    const diskPercent = parseFloat(sys.disk.usage_percentage);
                    document.getElementById('disk-value').textContent = sys.disk.usage_percentage;
                    document.getElementById('disk-detail').textContent = `${sys.disk.used} / ${sys.disk.total}`;
                    document.getElementById('disk-bar').style.width = diskPercent + '%';
                    
                    // DB Size
                    if (db.size_mb) {
                        document.getElementById('db-size').textContent = db.size_mb;
                        // Assuming 500MB is a "full" bar visual for shared hosting scale
                        const dbPercent = Math.min((db.size_mb / 500) * 100, 100); 
                        document.getElementById('db-size-bar').style.width = dbPercent + '%';
                        
                        if(db.size_mb > 100) {
                             document.getElementById('db-size-bar').classList.remove('bg-indigo-500');
                             document.getElementById('db-size-bar').classList.add('bg-amber-500');
                        }
                    }

                    // DB Status
                    const dbEl = document.getElementById('db-status');
                    const dbDot = document.getElementById('db-indicator-dot');
                    
                    if (db.status === 'connected') {
                        dbEl.textContent = 'OK';
                        dbEl.classList.remove('text-red-500');
                        dbEl.classList.add('text-green-500');
                        dbDot.className = 'w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_10px_#22c55e]';
                    } else {
                        dbEl.textContent = 'ERR';
                        dbEl.classList.add('text-red-500');
                        dbDot.className = 'w-2 h-2 rounded-full bg-red-500 mr-2 shadow-[0_0_10px_#ef4444]';
                        log("<span class='text-red-500'>DB Error: " + db.error + "</span>");
                    }

                    // Log Info
                    document.getElementById('log-size').textContent = logInfo.size;
                    const logDot = document.getElementById('log-status-dot');
                    const logText = document.getElementById('log-status-text');
                    
                    if (logInfo.raw_size > 50 * 1024 * 1024) { // 50MB Warning
                        logDot.className = "w-2 h-2 rounded-full bg-red-500 mr-2 blink";
                         logText.textContent = "Log Large!";
                         logText.classList.add('text-red-400');
                         log("<span class='text-red-500'>Warning: Log file large (" + logInfo.size + ")</span>");
                    } else {
                        logDot.className = "w-2 h-2 rounded-full bg-green-500 mr-2";
                        logText.textContent = "Log OK";
                        logText.classList.remove('text-red-400');
                    }
                };

                // Initial fetch
                fetchStats();
                log("Mobile monitor ready.");

                // Poll every 2 seconds
                setInterval(fetchStats, 2000);
            });
        </script>
    @endif
</body>
</html>
