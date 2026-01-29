import React from 'react';
import { 
    RiArrowLeftSLine, 
    RiArrowRightSLine, 
    RiZoomInLine, 
    RiZoomOutLine, 
    RiFullscreenLine 
} from 'remixicon/react/index'; // Ensure remixicon is installed or use font-awesome classes if necessary

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
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-600 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                >
                    <i className="fa-solid fa-chevron-left"></i>
                </button>
                <span className="text-xs font-bold text-gray-700 font-mono min-w-[80px] text-center select-none">
                    {label}
                </span>
                <button 
                    onClick={onNext} 
                    disabled={current >= total - 1}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-600 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                >
                    <i className="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            {/* Zoom Group */}
            <div className="flex items-center gap-2 border-r border-gray-200 pr-4 hidden sm:flex">
                 <button 
                    onClick={onZoomOut} 
                    disabled={zoomLevel <= 1}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-600 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 transition-colors"
                    title="Zoom Out"
                >
                    <i className="fa-solid fa-magnifying-glass-minus"></i>
                </button>
                 <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full select-none">
                    {Math.round(zoomLevel * 100)}%
                </span>
                 <button 
                    onClick={onZoomIn} 
                    disabled={zoomLevel >= 1.6}
                    className="w-8 h-8 rounded-full hover:bg-emerald-50 text-gray-600 hover:text-emerald-600 flex items-center justify-center disabled:opacity-30 transition-colors"
                    title="Zoom In"
                >
                    <i className="fa-solid fa-magnifying-glass-plus"></i>
                </button>
            </div>

            {/* Fullscreen */}
            <button 
                onClick={onToggleFullscreen}
                className="w-8 h-8 rounded-full hover:bg-emerald-50 text-emerald-600 flex items-center justify-center transition-colors"
                title={isFullscreen ? "Keluar Layar Penuh" : "Layar Penuh"}
            >
                <i className={`fa-solid ${isFullscreen ? 'fa-compress' : 'fa-expand'}`}></i>
            </button>

        </div>
    );
};

export default FlipbookControls;
