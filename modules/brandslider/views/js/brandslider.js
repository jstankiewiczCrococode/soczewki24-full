document.addEventListener('DOMContentLoaded', function () {
  const slider = document.querySelector('.brandslider__items');
  const leftButton = document.querySelector('#arrow-left');
  const rightButton = document.querySelector('#arrow-right');
  const dots = document.querySelectorAll('[data-slider-dot]');

  if (!slider) {
    return;
  }

  const isMobile = () => window.innerWidth < 768;

  const getStep = () => {
    const item = slider.querySelector('.brandslider__item');

    if (!item) {
      return 0;
    }

    const styles = window.getComputedStyle(slider);
    const gap = parseFloat(styles.columnGap) || 0;

    return item.getBoundingClientRect().width + gap;
  };

  const getMaxScroll = () => {
    return Math.max(0, slider.scrollWidth - slider.clientWidth);
  };

  const updateButtons = () => {
    if (!leftButton || !rightButton) {
      return;
    }

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

  const updateDots = () => {
    if (!dots.length) {
      return;
    }

    const maxScroll = getMaxScroll();

    const isAtEnd =
      maxScroll > 0 &&
      slider.scrollLeft >= maxScroll - 1;

    dots.forEach((dot, index) => {
      dot.classList.toggle(
        'is-active',
        index === 0 ? !isAtEnd : isAtEnd
      );
    });
  };

  const updateSliderState = () => {
    updateButtons();
    updateDots();
  };

  const scrollByOneItem = (direction) => {
    const step = getStep();

    if (!step) {
      return;
    }

    const maxScroll = getMaxScroll();
    const currentScroll = slider.scrollLeft;

    if (direction > 0) {
      slider.scrollTo({
        left: currentScroll >= maxScroll - 1 ? 0 : Math.min(currentScroll + step, maxScroll),
        behavior: 'smooth'
      });

      return;
    }

    slider.scrollTo({
      left: currentScroll <= 1 ? maxScroll : Math.max(currentScroll - step, 0),
      behavior: 'smooth'
    });
  };

  if (rightButton) {
    rightButton.addEventListener('click', () => {
      scrollByOneItem(1);
    });
  }

  if (leftButton) {
    leftButton.addEventListener('click', () => {
      scrollByOneItem(-1);
    });
  }

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      slider.scrollTo({
        left: index === 0 ? 0 : getMaxScroll(),
        behavior: 'smooth'
      });
    });
  });

  slider.addEventListener('scroll', updateSliderState);
  window.addEventListener('resize', updateSliderState);

  updateSliderState();
});