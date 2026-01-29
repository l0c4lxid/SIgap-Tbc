import React, { useCallback, useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import HTMLFlipBook from 'react-pageflip';
import FlipbookControls from './FlipbookControls';
import * as pdfjs from 'pdfjs-dist';

// Define the worker SRC explicitly
// @ts-ignore
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
pdfjs.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

interface FlipbookProps {
    initialPages: string[];
    pdfUrl: string;
}

const FlipbookViewer: React.FC<FlipbookProps> = ({ initialPages, pdfUrl }) => {
    const bookRef = useRef<any>(null);
    const [pages, setPages] = useState<string[]>(initialPages);
    const [currentPage, setCurrentPage] = useState(0);
    const [zoom, setZoom] = useState(1);
    const [isMobile, setIsMobile] = useState(window.innerWidth < 768);
    const [logs, setLogs] = useState<string[]>([]);
    const [isGenerating, setIsGenerating] = useState(false);
    const [loadingProgress, setLoadingProgress] = useState(0);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const addLog = (msg: string) => {
        console.log(msg);
        setLogs(prev => {
            const newLogs = [...prev, msg];
            if (newLogs.length > 50) newLogs.shift(); // Keep only last 50 lines
            return newLogs;
        });
    };

    // Initial Image Generation from PDF (Client Side Fallback)
    useEffect(() => {
        if (initialPages.length > 0) return;

        const generateImages = async () => {
            try {
                addLog(`[Start] initializing using URL: ${pdfUrl}`);
                setIsGenerating(true);

                // Check Fetch
                addLog("[Fetch] Fetching PDF file...");
                const response = await fetch(pdfUrl);
                if (!response.ok) {
                    throw new Error(`Failed to fetch PDF: ${response.status} ${response.statusText}`);
                }
                const pdfData = await response.arrayBuffer();
                addLog(`[Fetch] Data received (${pdfData.byteLength} bytes)`);

                // Configure Worker
                addLog("[PDFJS] Initializing Document...");
                
                const loadingTask = pdfjs.getDocument({
                    data: pdfData,
                    cMapUrl: `https://cdn.jsdelivr.net/npm/pdfjs-dist@${pdfjs.version}/cmaps/`,
                    cMapPacked: true,
                    verbosity: 0, // Suppress warnings like "Knockout groups"
                });

                const pdf = await loadingTask.promise;
                const total = pdf.numPages;
                addLog(`[PDFJS] Document Loaded. Total Pages: ${total}`);

                if (!total || total === 0) throw new Error("PDF has 0 pages.");

                const generatedPages: string[] = [];
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d', { willReadFrequently: true });
                if (!context) throw new Error("Canvas context not available");

                for (let i = 1; i <= total; i++) {
                    addLog(`[Render] Processing page ${i}/${total}...`);
                    
                    try {
                        const page = await pdf.getPage(i);
                        // Reduce scale slightly for performance/stability
                        const viewport = page.getViewport({ scale: 1.0 }); // Standard res (enough for screen)
                        
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        await page.render({ canvasContext: context, viewport: viewport }).promise;
                        generatedPages.push(canvas.toDataURL('image/jpeg', 0.8)); // Slightly lower quality for speed
                        
                        page.cleanup(); // Free memory
                        
                        setLoadingProgress(Math.round((i / total) * 100));
                        
                        // Critical: Unblock Main Thread
                        await new Promise(r => setTimeout(r, 10)); 
                    } catch (err: any) {
                        addLog(`[Warn] Error rendering page ${i}: ${err.message}`);
                    }
                }

                addLog(`[Success] Generated ${generatedPages.length} images.`);
                setPages(generatedPages);
                setIsGenerating(false);
            } catch (error: any) {
                addLog(`[Error] ${error.message}`);
                console.error("PDF Generation Error (Detailed):", error);
                setErrorMessage(error.message || "Unknown error occurred");
                setIsGenerating(false);
            }
        };

        generateImages();
    }, [initialPages, pdfUrl]);

    // Fullscreen & Resizing Logic
    const containerRef = useRef<HTMLDivElement>(null);
    const [containerSize, setContainerSize] = useState({ width: 0, height: 0 });
    const [isFullscreen, setIsFullscreen] = useState(false); // Native status
    const [isPseudoFullscreen, setIsPseudoFullscreen] = useState(false); // Fallback status

    const toggleFullscreen = useCallback(async () => {
        // 1. Try Native Fullscreen first
        if (!document.fullscreenElement && !isPseudoFullscreen) {
            try {
                if (containerRef.current && containerRef.current.requestFullscreen) {
                    await containerRef.current.requestFullscreen();
                } else {
                    // Fallback for iOS / Unsupported browsers
                    setIsPseudoFullscreen(true);
                    setIsMobile(true); // Force re-check
                }
            } catch (err) {
                console.warn("Native fullscreen failed, using fallback:", err);
                setIsPseudoFullscreen(true);
            }
        } else {
            // Exit Logic
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(err => console.error(err));
            }
            if (isPseudoFullscreen) {
                setIsPseudoFullscreen(false);
            }
        }
    }, [isPseudoFullscreen]);

    // Sync Native State
    useEffect(() => {
        const handleFullscreenChange = () => {
            setIsFullscreen(!!document.fullscreenElement);
        };
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange); // Safari legacy
        return () => {
             document.removeEventListener('fullscreenchange', handleFullscreenChange);
             document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
        };
    }, []);

    // Resize Observer for Responsive Flipbook
    useEffect(() => {
        if (!containerRef.current) return;

        const observer = new ResizeObserver(entries => {
            for (let entry of entries) {
                const { width, height } = entry.contentRect;
                setContainerSize({ width, height });
                setIsMobile(width < 768); // Update mobile state
                setZoom(1); 
            }
        });

        observer.observe(containerRef.current);
        return () => observer.disconnect();
    }, []);

    // ... (getBookDimensions stays same) ...
    const getBookDimensions = () => {
        if (containerSize.width === 0) return { width: 300, height: 400 };

        if (isMobile) {
            const width = Math.min(containerSize.width * 0.96, 600);
            const height = width * 1.414;
            return { width, height };
        } else {
            // Desktop: Use mostly full available space
            const availableWidth = containerSize.width * 0.96;
            const availableHeight = containerSize.height * 0.96;
            
            // Calculate based on height first (A4 ratio)
            let pageWidth = availableHeight / 1.414;
            
            // Check if 2 pages fit in available width
            if (pageWidth * 2 > availableWidth) {
                pageWidth = availableWidth / 2;
            }
            
            return { width: pageWidth, height: pageWidth * 1.414 };
        }
    };

    const dims = getBookDimensions();

    const onFlip = useCallback((e: any) => setCurrentPage(e.data), []);

    const handleZoom = (direction: 'in' | 'out') => {
        setZoom(prev => Math.min(Math.max(direction === 'in' ? prev + 0.15 : prev - 0.15, 1), 2.0));
    };

    const activeFullscreen = isFullscreen || isPseudoFullscreen;

    // Listen for External Trigger (from Blade Button)
    useEffect(() => {
        const handleExternalToggle = () => {
            toggleFullscreen();
        };
        window.addEventListener('toggle-flipbook-fullscreen', handleExternalToggle);
        return () => window.removeEventListener('toggle-flipbook-fullscreen', handleExternalToggle);
    }, [toggleFullscreen]);

    return (
        <div 
            ref={containerRef}
            className={`relative flex flex-col items-center justify-center bg-[#1e1e1e] transition-all duration-300 ${activeFullscreen ? 'fixed inset-0 z-[2147483647] w-screen h-screen p-0 bg-black' : 'w-full h-[calc(100vh-10rem)] md:h-[75vh] rounded-2xl border border-gray-800'}`}
        >
            
            {/* Top Controls: ALWAYS VISIBLE */}
            <div className={`absolute top-4 z-20 w-full flex justify-center px-4 pointer-events-none`}>
                 <div className="pointer-events-auto">
                     <FlipbookControls 
                        current={currentPage} 
                        total={pages.length}
                        onPrev={() => bookRef.current?.pageFlip().flipPrev()} 
                        onNext={() => bookRef.current?.pageFlip().flipNext()}
                        onZoomIn={() => handleZoom('in')}
                        onZoomOut={() => handleZoom('out')}
                        zoomLevel={zoom}
                        isMobile={isMobile}
                        isFullscreen={activeFullscreen}
                        onToggleFullscreen={toggleFullscreen}
                     />
                 </div>
            </div>

            {/* Stage Area */}
            <div className="relative w-full h-full overflow-hidden flex items-center justify-center p-4">
                
                {/* 1. Generating State */}
                {isGenerating && (
                     <div className="w-full h-full flex flex-col items-center justify-center text-white/70 bg-[#1e1e1e]">
                         <div className="w-16 h-16 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                         <p className="font-bold text-lg">Memproses Materi...</p>
                         <p className="text-sm font-mono mt-2">{loadingProgress}% Selesai</p>
                         <div className="mt-8 bg-gray-900/50 p-4 rounded-lg w-full max-w-lg font-mono text-[10px] text-left text-gray-400 max-h-32 overflow-hidden flex flex-col-reverse">
                            {logs.slice(-5).map((log, i) => <div key={i}>{log}</div>)}
                         </div>
                     </div>
                )}

                {/* 2. Error State */}
                {!isGenerating && errorMessage && (
                    <div className="text-white text-center p-8 bg-[#1e1e1e] w-full h-full flex flex-col items-center justify-center">
                        <div className="text-red-400 text-4xl mb-4"><i className="ri-error-warning-line"></i></div>
                        <h3 className="text-xl font-bold mb-2">Gagal memuat materi</h3>
                        <p className="text-sm opacity-80 max-w-md mx-auto mb-4">{errorMessage}</p>
                        <a href={pdfUrl} className="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors">
                            Unduh PDF Saja
                        </a>
                    </div>
                )}

                {/* 3. Empty State (should not happen if generic success) */}
                {!isGenerating && !errorMessage && pages.length === 0 && (
                     <div className="text-white text-center p-8 flex flex-col items-center justify-center h-full w-full">
                        <i className="ri-file-search-line text-4xl mb-4 opacity-50"></i>
                        <p className="mb-4">Tidak ada halaman untuk ditampilkan.</p>
                        <div className="bg-black/50 p-4 rounded text-xs font-mono text-left opacity-70">
                            <p>Status: Ready</p>
                            <p>Pages: 0</p>
                            <p>PDF: {pdfUrl || 'None'}</p>
                        </div>
                     </div>
                )}

                {/* 4. Content State */}
                {!isGenerating && !errorMessage && pages.length > 0 && (
                    <div style={{ transform: `scale(${zoom})`, transformOrigin: 'center center' }}>
                        <HTMLFlipBook
                            width={dims.width}
                            height={dims.height}
                            size="fixed"
                            minWidth={200}
                            maxWidth={3000}
                            minHeight={300}
                            maxHeight={3000}
                            maxShadowOpacity={0.5}
                            showCover={true}
                            mobileScrollSupport={true}
                            className="shadow-2xl"
                            onFlip={onFlip}
                            ref={bookRef}
                            style={{ margin: '0 auto' }}
                            startPage={0}
                            drawShadow={true}
                            flippingTime={1000}
                            usePortrait={isMobile}
                            startZIndex={0}
                            autoSize={true}
                            clickEventForward={true}
                            useMouseEvents={true}
                            swipeDistance={30}
                            showPageCorners={true}
                            disableFlipByClick={false}
                        >
                            {pages.map((url, index) => (
                                <div key={index} className="bg-white shadow-sm overflow-hidden border-r border-gray-200/50">
                                     <div className="w-full h-full flex items-center justify-center bg-gray-50">
                                        <img 
                                            src={url} 
                                            alt={`Page ${index + 1}`} 
                                            className="w-full h-full object-contain select-none" 
                                            loading="eager" 
                                            draggable={false}
                                        />
                                        <div className="absolute bottom-2 right-2 text-[10px] text-gray-400 font-mono">{index + 1}</div>
                                     </div>
                                </div>
                            ))}
                        </HTMLFlipBook>
                    </div>
                )}
            </div>
        </div>
    );
};

// Mount
const container = document.getElementById('materiFlipbook');
if (container) {
    const root = createRoot(container);
    const pages = JSON.parse(container.getAttribute('data-pages') || '[]');
    const pdfUrl = container.getAttribute('data-pdf-url') || '';
    root.render(<FlipbookViewer initialPages={pages} pdfUrl={pdfUrl} />);
}
