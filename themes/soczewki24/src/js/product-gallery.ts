import {Carousel} from 'bootstrap';
import SelectorsMap from '@constants/selectors-map';

type ModalShowEvent = Event & {relatedTarget?: HTMLElement | null};

// Zdjecia w siatce galerii otwieraja wspolny modal; tu ustawiamy slajd na klikniete zdjecie.
// Nasluch na document, wiec przetrwa podmiane galerii po zmianie wariantu (AJAX).
const initProductGallery = () => {
  document.addEventListener('show.bs.modal', (event) => {
    const modal = event.target as HTMLElement;

    if (!modal.matches(SelectorsMap.product.productImagesModal)) return;

    const index = Number((event as ModalShowEvent).relatedTarget?.dataset.galleryIndex);
    const carouselElement = modal.querySelector<HTMLElement>(SelectorsMap.product.productImagesModalCarousel);

    if (Number.isNaN(index) || !carouselElement) return;

    Carousel.getOrCreateInstance(carouselElement).to(index);
  });
};

export default initProductGallery;
