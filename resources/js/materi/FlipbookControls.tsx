import React from 'react';

interface ControlsProps {
    current: number;
    total: number;
    onPrev: () => void;
    onNext: () => void;
    onZoomIn: () => void;
    onZoomOut: () => void;
    zoomLevel: number;
    isMobile: boolean;
    isFullscreen: boolean;
    onToggleFullscreen: () => void;
}

const FlipbookControls: React.FC<ControlsProps> = ({ 
    current, 
    total, 
    onPrev, 
    onNext, 
    onZoomIn, 
    onZoomOut,
    zoomLevel,
    isMobile,
    isFullscreen,
    onToggleFullscreen
}) => {
    // Determine Page Label
    const label = isMobile
        ? `Halaman ${current + 1} / ${total}`
        : `Halaman ${current + 1}${current + 1 < total ? `–${current + 2}` : ''} / ${total}`;

    return (
        <div className="bg-white/90 backdrop-blur-md shadow-lg border border-white/50 rounded-full px-4 py-2 flex items-center gap-4 transition-all hover:scale-[1.02]">
            
            {/* Nav Group */}
            <div className="flex items-center gap-2 border-r border-gray-200 pr-4">
                <button 
                    onClick={onPrev} 
                    disabled={current === 0}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-700 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                >
                    <i className="ri-arrow-left-s-line text-2xl"></i>
                </button>
                <span className="text-xs font-bold text-gray-800 font-mono min-w-[80px] text-center select-none">
                    {label}
                </span>
                <button 
                    onClick={onNext} 
                    disabled={current >= total - 1}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-700 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                >
                    <i className="ri-arrow-right-s-line text-2xl"></i>
                </button>
            </div>

            {/* Zoom Group */}
            <div className="flex items-center gap-2 border-r border-gray-200 pr-4 hidden sm:flex">
                 <button 
                    onClick={onZoomOut} 
                    disabled={zoomLevel <= 1}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-700 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 transition-colors"
                    title="Zoom Out"
                >
                    <i className="ri-zoom-out-line text-xl"></i>
                </button>
                 <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full select-none">
                    {Math.round(zoomLevel * 100)}%
                </span>
                 <button 
                    onClick={onZoomIn} 
                    disabled={zoomLevel >= 1.6}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-700 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 transition-colors"
                    title="Zoom In"
                >
                    <i className="ri-zoom-in-line text-xl"></i>
                </button>
            </div>

            {/* Fullscreen */}
            <button 
                onClick={onToggleFullscreen}
                className="w-8 h-8 rounded-full hover:bg-emerald-50 text-emerald-600 flex items-center justify-center transition-colors"
                title={isFullscreen ? "Keluar Layar Penuh" : "Layar Penuh"}
            >
                <i className={`${isFullscreen ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line'} text-xl`}></i>
            </button>

        </div>
    );
};

export default FlipbookControls;
