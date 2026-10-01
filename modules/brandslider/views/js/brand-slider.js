const initBrandSlider = (): void => {
  const slider = document.querySelector<HTMLElement>('.brandslider__items');
  const leftButton =
    document.querySelector<HTMLButtonElement>('#arrow-left');
  const rightButton =
    document.querySelector<HTMLButtonElement>('#arrow-right');
  const dots = document.querySelectorAll<HTMLButtonElement>(
    '[data-slider-dot]',
  );

  if (!slider || !leftButton || !rightButton) {
    return;
  }

  const isMobile = (): boolean => window.innerWidth < 768;

  const getStep = (): number => {
    const item =
      slider.querySelector<HTMLElement>('.brandslider__item');

    if (!item) {
      return 0;
    }

    const styles = window.getComputedStyle(slider);
    const gap = parseFloat(styles.columnGap) || 0;

    return item.getBoundingClientRect().width + gap;
  };

  const getMaxScroll = (): number => {
    return Math.max(
      0,
      slider.scrollWidth - slider.clientWidth,
    );
  };

  const updateButtons = (): void => {
    if (isMobile()) {
      leftButton.style.display = 'none';
      rightButton.style.display = 'none';

      return;
    }

    const maxScroll = getMaxScroll();

    leftButton.style.display =
      slider.scrollLeft > 1 ? 'flex' : 'none';

    rightButton.style.display =
      slider.scrollLeft < maxScroll - 1 ? 'flex' : 'none';
  };

  const updateDots = (): void => {
    if (!dots.length) {
      return;
    }

    const maxScroll = getMaxScroll();

    const isAtEnd =
      maxScroll > 0 &&
      slider.scrollLeft >= maxScroll - 1;

    dots.forEach((dot, index) => {
      const isActive =
        index === 0
          ? !isAtEnd
          : isAtEnd;

      dot.classList.toggle('is-active', isActive);
    });
  };

  const updateSliderState = (): void => {
    updateButtons();
    updateDots();
  };

const scrollByOneItem = (direction: number): void => {
  const step = getStep();

  if (!step) {
    return;
  }

  const maxScroll = getMaxScroll();

  const currentScroll = slider.scrollLeft;

  if (direction > 0) {
    if (currentScroll >= maxScroll - 1) {
      slider.scrollTo({
        left: 0,
        behavior: 'smooth',
      });

      return;
    }

    slider.scrollTo({
      left: Math.min(
        currentScroll + step,
        maxScroll,
      ),
      behavior: 'smooth',
    });

    return;
  }

  if (currentScroll <= 1) {
    slider.scrollTo({
      left: maxScroll,
      behavior: 'smooth',
    });

    return;
  }

  slider.scrollTo({
    left: Math.max(
      currentScroll - step,
      0,
    ),
    behavior: 'smooth',
  });
};

  const scrollToStart = (): void => {
    slider.scrollTo({
      left: 0,
      behavior: 'smooth',
    });
  };

  const scrollToEnd = (): void => {
    slider.scrollTo({
      left: getMaxScroll(),
      behavior: 'smooth',
    });
  };

  rightButton.addEventListener('click', () => {
    scrollByOneItem(1);
  });

  leftButton.addEventListener('click', () => {
    scrollByOneItem(-1);
  });

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      if (index === 0) {
        scrollToStart();
        return;
      }

      scrollToEnd();
    });
  });

  slider.addEventListener('scroll', () => {
    updateSliderState();
  });

  window.addEventListener('resize', () => {
    updateSliderState();
  });

  // DRAG
  let isDown = false;
  let startX = 0;
  let scrollLeft = 0;

  slider.addEventListener('mousedown', (event) => {
    if (isMobile()) {
      return;
    }

    isDown = true;

    slider.classList.add('is-dragging');

    startX = event.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });

  slider.addEventListener('mouseleave', () => {
    isDown = false;

    slider.classList.remove('is-dragging');
  });

  slider.addEventListener('mouseup', () => {
    isDown = false;

    slider.classList.remove('is-dragging');
  });

  slider.addEventListener('mousemove', (event) => {
    if (!isDown) {
      return;
    }

    event.preventDefault();

    const x = event.pageX - slider.offsetLeft;
    const walk = (x - startX) * 1.5;

    slider.scrollLeft = scrollLeft - walk;
  });

  updateSliderState();
};

export default initBrandSlider;