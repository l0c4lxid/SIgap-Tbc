import * as pdfjsLib from "pdfjs-dist";
import workerSrc from "pdfjs-dist/build/pdf.worker.min.mjs?url";

const initFlipbook = () => {
  const container = document.getElementById("flipbook");
  const canvasLeft = document.getElementById("flipbookCanvasLeft");
  const canvasRight = document.getElementById("flipbookCanvasRight");
  const prevBtn = document.getElementById("flipPrev");
  const nextBtn = document.getElementById("flipNext");
  const fullscreenBtn = document.getElementById("flipFullscreen");
  const pageLabel = document.getElementById("flipPageLabel");
  const loadingMessage = container?.querySelector(".flipbook-loading");

  if (!container || !canvasLeft || !canvasRight) {
    return;
  }

  const pdfUrl = container.dataset.pdfUrl;
  if (!pdfUrl) {
    if (loadingMessage) {
      loadingMessage.textContent = "PDF tidak ditemukan.";
    }
    return;
  }

  pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc;

  let pdfDoc = null;
  let currentPage = 1;
  let totalPages = 1;
  let isRendering = false;

  const getScale = (page, canvas) => {
    const viewport = page.getViewport({ scale: 1 });
    const parent = canvas.parentElement;
    if (!parent) {
      return 1;
    }
    const availableWidth = parent.clientWidth * 0.95;
    const availableHeight = parent.clientHeight * 0.95;
    const scaleX = availableWidth / viewport.width;
    const scaleY = availableHeight / viewport.height;
    return Math.min(scaleX, scaleY, 2.0);
  };

  const renderPage = async (pageNumber, canvas, direction = "next") => {
    if (!pdfDoc || isRendering) {
      return;
    }
    isRendering = true;

    const page = await pdfDoc.getPage(pageNumber);
    const scale = getScale(page, canvas);
    const viewport = page.getViewport({ scale });
    const context = canvas.getContext("2d");

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    canvas.classList.remove("flipbook-animate-next", "flipbook-animate-prev", "flipbook-animate-load");
    if (direction === "next") {
      canvas.classList.add("flipbook-animate-next");
    } else if (direction === "prev") {
      canvas.classList.add("flipbook-animate-prev");
    } else if (direction === "load") {
      canvas.classList.add("flipbook-animate-load");
    }

    await page.render({ canvasContext: context, viewport }).promise;

    isRendering = false;
  };

  const renderSpread = async (startPage, direction = "next") => {
    if (!pdfDoc) {
      return;
    }
    const leftPage = startPage;
    const rightPage = startPage + 1;

    if (leftPage > totalPages) {
      return;
    }

    if (loadingMessage) {
      loadingMessage.textContent = `Memuat halaman ${leftPage}${rightPage <= totalPages ? `-${rightPage}` : ""}...`;
    }

    const leftDirection = direction === "load" ? "load" : (direction === "next" ? "prev" : "next");
    await renderPage(leftPage, canvasLeft, leftDirection);

    if (rightPage <= totalPages) {
      await renderPage(rightPage, canvasRight, direction === "load" ? "load" : direction);
      canvasRight.style.visibility = "visible";
    } else {
      const context = canvasRight.getContext("2d");
      context.clearRect(0, 0, canvasRight.width, canvasRight.height);
      canvasRight.style.visibility = "hidden";
    }

    currentPage = leftPage;
    if (pageLabel) {
      const label = rightPage <= totalPages
        ? `${leftPage}-${rightPage}`
        : `${leftPage}`;
      pageLabel.textContent = label;
    }
    if (prevBtn) {
      prevBtn.disabled = currentPage <= 1;
    }
    if (nextBtn) {
      nextBtn.disabled = currentPage + 1 >= totalPages;
    }
  };

  pdfjsLib
    .getDocument(pdfUrl)
    .promise.then((pdf) => {
      pdfDoc = pdf;
      totalPages = pdf.numPages;
      if (loadingMessage) {
        loadingMessage.remove();
      }
      return renderSpread(1, "load");
    })
    .catch(() => {
      if (loadingMessage) {
        loadingMessage.textContent = "Gagal memuat PDF. Coba muat ulang.";
      }
    });

  prevBtn?.addEventListener("click", () => {
    const target = Math.max(1, currentPage - 2);
    renderSpread(target, "prev");
  });

  nextBtn?.addEventListener("click", () => {
    const target = currentPage + 2;
    if (target <= totalPages) {
      renderSpread(target, "next");
    }
  });

  let touchStartX = 0;
  let touchStartY = 0;
  let isSwiping = false;

  container.addEventListener("touchstart", (event) => {
    const touch = event.touches?.[0];
    if (!touch) {
      return;
    }
    touchStartX = touch.clientX;
    touchStartY = touch.clientY;
    isSwiping = true;
  }, { passive: true });

  container.addEventListener("touchmove", (event) => {
    if (!isSwiping) {
      return;
    }
    const touch = event.touches?.[0];
    if (!touch) {
      return;
    }
    const deltaX = touch.clientX - touchStartX;
    const deltaY = touch.clientY - touchStartY;
    if (Math.abs(deltaY) > Math.abs(deltaX)) {
      isSwiping = false;
    }
  }, { passive: true });

  container.addEventListener("touchend", (event) => {
    if (!isSwiping) {
      return;
    }
    const touch = event.changedTouches?.[0];
    if (!touch) {
      return;
    }
    const deltaX = touch.clientX - touchStartX;
    if (deltaX < -40) {
      const target = currentPage + 2;
      if (target <= totalPages) {
        renderSpread(target, "next");
      }
    } else if (deltaX > 40) {
      const target = Math.max(1, currentPage - 2);
      renderSpread(target, "prev");
    }
    isSwiping = false;
  }, { passive: true });

  const exitFullscreenUI = () => {
    const wrapper = container.closest(".flip-embed");
    if (!wrapper) {
      return;
    }
    wrapper.classList.remove("flipbook-fullscreen", "flipbook-force-landscape");
    document.body.classList.remove("flipbook-mode");
    fullscreenBtn?.setAttribute("aria-label", "Layar penuh");
    if (fullscreenBtn) {
      fullscreenBtn.innerHTML = `<i class="ri-fullscreen-line" id="flipFullscreenIcon"></i>`;
    }
    requestAnimationFrame(() => {
      renderSpread(currentPage, "next");
    });
  };

  fullscreenBtn?.addEventListener("click", async () => {
    const wrapper = container.closest(".flip-embed");
    if (!wrapper) {
      return;
    }
    const shouldEnter = !wrapper.classList.contains("flipbook-fullscreen");
    const isFullscreen = wrapper.classList.toggle("flipbook-fullscreen", shouldEnter);
    wrapper.classList.toggle(
      "flipbook-force-landscape",
      shouldEnter && window.matchMedia("(max-width: 768px)").matches
    );
    document.body.classList.toggle("flipbook-mode", isFullscreen);
    fullscreenBtn.setAttribute("aria-label", isFullscreen ? "Keluar layar penuh" : "Layar penuh");
    fullscreenBtn.innerHTML = `<i class="${isFullscreen ? "ri-fullscreen-exit-line" : "ri-fullscreen-line"}" id="flipFullscreenIcon"></i>`;
    if (shouldEnter && wrapper.requestFullscreen) {
      try {
        await wrapper.requestFullscreen();
        if (window.matchMedia("(max-width: 768px)").matches) {
          wrapper.classList.add("flipbook-force-landscape");
          if (screen.orientation && screen.orientation.lock) {
            await screen.orientation.lock("landscape");
          }
        }
      } catch {
        // ignore
      }
    } else if (!shouldEnter) {
      if (document.fullscreenElement) {
        try {
          await document.exitFullscreen();
        } catch {
          // ignore
        }
      } else {
        exitFullscreenUI();
      }
    }
  });

  document.addEventListener("fullscreenchange", () => {
    const wrapper = container.closest(".flip-embed");
    if (!wrapper) {
      return;
    }
    const isFullscreen = document.fullscreenElement === wrapper;
    wrapper.classList.toggle("flipbook-fullscreen", isFullscreen);
    wrapper.classList.toggle(
      "flipbook-force-landscape",
      isFullscreen && window.matchMedia("(max-width: 768px)").matches
    );
    document.body.classList.toggle("flipbook-mode", isFullscreen);
    fullscreenBtn?.setAttribute("aria-label", isFullscreen ? "Keluar layar penuh" : "Layar penuh");
    if (fullscreenBtn) {
      fullscreenBtn.innerHTML = `<i class="${isFullscreen ? "ri-fullscreen-exit-line" : "ri-fullscreen-line"}" id="flipFullscreenIcon"></i>`;
    }
    if (!isFullscreen) {
      exitFullscreenUI();
    }
  });

  window.addEventListener("resize", () => {
    renderSpread(currentPage, "next");
  });

  window.addEventListener("orientationchange", () => {
    setTimeout(() => {
      renderSpread(currentPage, "next");
    }, 250);
  });

  container.addEventListener("dblclick", () => {
    fullscreenBtn?.click();
  });

  const handleEscape = (event) => {
    if (event.key !== "Escape") {
      return;
    }
    if (document.fullscreenElement) {
      document.exitFullscreen().catch(() => {});
      return;
    }
    exitFullscreenUI();
  };

  document.addEventListener("keydown", handleEscape, true);
  window.addEventListener("keydown", handleEscape, true);
  document.addEventListener("keyup", handleEscape, true);
};

document.addEventListener("DOMContentLoaded", initFlipbook);
