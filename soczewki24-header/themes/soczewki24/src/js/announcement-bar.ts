const COOKIE_MAX_AGE = 60 * 60 * 24 * 30;

const initAnnouncementBar = () => {
  const {Theme} = window;
  const {announcementBar: AnnouncementBarMap} = Theme.selectors;

  const bar = document.querySelector<HTMLElement>(AnnouncementBarMap.bar);
  const closeButton = bar?.querySelector<HTMLButtonElement>(AnnouncementBarMap.close);

  if (!bar || !closeButton) {
    return;
  }

  closeButton.addEventListener('click', () => {
    const {announcementCookie, announcementId} = bar.dataset;

    if (announcementCookie && announcementId) {
      document.cookie = `${announcementCookie}=${announcementId}; path=/; max-age=${COOKIE_MAX_AGE}; SameSite=Lax`;
    }

    bar.remove();

    // Przycisk znika z DOM, wiec bez tego focus wraca na <body> (WCAG 2.4.3).
    document.querySelector<HTMLElement>(AnnouncementBarMap.focusAfterClose)?.focus();
  });
};

export default initAnnouncementBar;
