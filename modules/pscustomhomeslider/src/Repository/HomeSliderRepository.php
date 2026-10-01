<?php

namespace PrestaShop\Module\PsCustomHomeSlider\Repository;

use Db;
use DbQuery;
use Language;

class HomeSliderRepository
{
    private const TABLE = 'custom_home_slider';
    private const TABLE_LANG = 'custom_home_slider_lang';

    /**
     * Pobiera wszystkie slajdy wraz z tłumaczeniami.
     */
    public function findAll(): array
    {
        $idLang = (int) \Context::getContext()->language->id;

        $query = new DbQuery();

        $query->select('s.*');
        $query->select('sl.title, sl.description, sl.button_text');

        $query->from(self::TABLE, 's');
        $query->leftJoin(
            self::TABLE_LANG,
            'sl',
            'sl.id_slide = s.id_slide AND sl.id_lang = ' . $idLang
        );

        $query->orderBy('s.position ASC');
        $query->orderBy('s.id_slide ASC');

        return Db::getInstance()->executeS($query) ?: [];
    }

    /**
     * Pobiera jeden slajd.
     */
    public function findOne(int $idSlide): ?array
    {
        $query = new DbQuery();

        $query->select('s.*');
        $query->from(self::TABLE, 's');
        $query->where('s.id_slide = ' . $idSlide);

        $slide = Db::getInstance()->getRow($query);

        if (!$slide) {
            return null;
        }

        $slide['translations'] = $this->getTranslations($idSlide);

        return $slide;
    }

    /**
     * Pobiera tłumaczenia slajdu.
     */
    public function getTranslations(int $idSlide): array
    {
        $query = new DbQuery();

        $query->select('*');
        $query->from(self::TABLE_LANG);
        $query->where('id_slide = ' . $idSlide);

        $rows = Db::getInstance()->executeS($query) ?: [];

        $translations = [];

        foreach ($rows as $row) {
            $translations[(int) $row['id_lang']] = $row;
        }

        return $translations;
    }

    /**
     * Dodaje slajd.
     */
    public function create(array $data, array $translations): int
    {
        $db = Db::getInstance();

        $now = date('Y-m-d H:i:s');

        $slideData = [
            'image' => $data['image'] ?? null,
            'url' => $data['url'] ?? null,
            'position' => (int) ($data['position'] ?? 0),
            'active' => !empty($data['active']) ? 1 : 0,
            'date_add' => $now,
            'date_upd' => $now,
        ];

        if (!$db->insert(self::TABLE, $slideData)) {
            return 0;
        }

        $idSlide = (int) $db->Insert_ID();

        if (!$this->saveTranslations($idSlide, $translations)) {
            $db->delete(
                self::TABLE,
                'id_slide = ' . $idSlide
            );

            return 0;
        }

        return $idSlide;
    }

    /**
     * Aktualizuje slajd.
     */
    public function update(
        int $idSlide,
        array $data,
        array $translations
    ): bool {
        $db = Db::getInstance();

        $slideData = [
            'image' => $data['image'] ?? null,
            'url' => $data['url'] ?? null,
            'position' => (int) ($data['position'] ?? 0),
            'active' => !empty($data['active']) ? 1 : 0,
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        if (!$db->update(
            self::TABLE,
            $slideData,
            'id_slide = ' . $idSlide
        )) {
            return false;
        }

        return $this->saveTranslations($idSlide, $translations);
    }

    /**
     * Zapisuje tłumaczenia.
     */
    private function saveTranslations(
        int $idSlide,
        array $translations
    ): bool {
        $db = Db::getInstance();

        foreach ($translations as $idLang => $translation) {
            $idLang = (int) $idLang;

            $data = [
                'id_slide' => $idSlide,
                'id_lang' => $idLang,
                'title' => $translation['title'] ?? '',
                'description' => $translation['description'] ?? null,
                'button_text' => $translation['button_text'] ?? null,
            ];

            $exists = $db->getValue(
                'SELECT COUNT(*)
                 FROM `' . _DB_PREFIX_ . self::TABLE_LANG . '`
                 WHERE id_slide = ' . $idSlide . '
                 AND id_lang = ' . $idLang
            );

            if ($exists) {
                if (!$db->update(
                    self::TABLE_LANG,
                    $data,
                    'id_slide = ' . $idSlide .
                    ' AND id_lang = ' . $idLang
                )) {
                    return false;
                }
            } else {
                if (!$db->insert(self::TABLE_LANG, $data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Usuwa slajd wraz z tłumaczeniami.
     */
    public function delete(int $idSlide): bool
    {
        $db = Db::getInstance();

        if (!$db->delete(
            self::TABLE_LANG,
            'id_slide = ' . $idSlide
        )) {
            return false;
        }

        return $db->delete(
            self::TABLE,
            'id_slide = ' . $idSlide
        );
    }

    /**
     * Zmienia kolejność slajdu.
     */
    public function updatePosition(
        int $idSlide,
        int $position
    ): bool {
        return Db::getInstance()->update(
            self::TABLE,
            [
                'position' => $position,
                'date_upd' => date('Y-m-d H:i:s'),
            ],
            'id_slide = ' . $idSlide
        );
    }

    /**
     * Pobiera aktywne slajdy dla front-office.
     */
    public function findActive(): array
    {
        $idLang = (int) \Context::getContext()->language->id;

        $query = new DbQuery();

        $query->select('s.*');
        $query->select('sl.title, sl.description, sl.button_text');

        $query->from(self::TABLE, 's');

        $query->innerJoin(
            self::TABLE_LANG,
            'sl',
            'sl.id_slide = s.id_slide AND sl.id_lang = ' . $idLang
        );

        $query->where('s.active = 1');

        $query->orderBy('s.position ASC');
        $query->orderBy('s.id_slide ASC');

        return Db::getInstance()->executeS($query) ?: [];
    }
}