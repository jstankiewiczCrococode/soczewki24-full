import slider1 from '../../img/home/slider-1.png';
import slider2 from '../../img/home/slide-2.webp';

const initHomeSlider = (): void => {
  const slider = document.querySelector<HTMLElement>('[data-home-slider]');

  if (!slider) {
    return;
  }

  const slides = Array.from(
    slider.querySelectorAll<HTMLElement>('.home-slider__slide')
  );

  const images = [slider1, slider2];

  if (!slides.length) {
    return;
  }

  slides.forEach((slide, index) => {
    const imageContainer = slide.querySelector<HTMLElement>(
      '.home-slider__image'
    );

    const image = images[index];

    if (imageContainer && image) {
      const img = document.createElement('img');

      img.src = image;
      img.alt = index === 0 ? 'Soczewki kontaktowe' : 'Soczewki';

      imageContainer.appendChild(img);
    }

    slide.classList.toggle('is-active', index === 0);
  });

  let currentSlide = 0;

  const showSlide = (index: number): void => {
    currentSlide = (index + slides.length) % slides.length;

    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle(
        'is-active',
        slideIndex === currentSlide
      );
    });
  };

  const nextSlide = (): void => {
    showSlide(currentSlide + 1);
  };

  const previousSlide = (): void => {
    showSlide(currentSlide - 1);
  };

  const nextButton = slider.querySelector<HTMLButtonElement>(
    '[data-slider-next]'
  );

  const previousButton = slider.querySelector<HTMLButtonElement>(
    '[data-slider-prev]'
  );

  nextButton?.addEventListener('click', nextSlide);
  previousButton?.addEventListener('click', previousSlide);

  const dots = Array.from(
    slider.querySelectorAll<HTMLButtonElement>('[data-slider-dot]')
  );

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      showSlide(index);
    });
  });

  window.setInterval(nextSlide, 5000);
};

export default initHomeSlider;