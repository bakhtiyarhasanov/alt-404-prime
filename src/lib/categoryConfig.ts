export type CategoryConfig = {
  slug: string;
  label: string;
  showOnSite: boolean;
  sortOrder: number;
  parentSlug?: string;

  // SEO overrides for the category page
  metaTitle?: string;
  metaDescription?: string;

  // If non-empty, CategoryPage will show only these tag chips.
  curatedTags?: string[];
};

export const DEFAULT_CATEGORIES: CategoryConfig[] = [
  {
    slug: 'texnologiya',
    label: 'Texnologiya Xəbərləri',
    showOnSite: true,
    sortOrder: 10,
    metaTitle: 'Texnologiya xəbərləri - Smartfonlar, Süni intellekt, Startap | alt404',
    metaDescription:
      'Azərbaycanda texnologiya xəbərləri, smartfon icmalları, süni intellekt və startap yenilikləri. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'elm-gundem',
    label: 'Elm',
    showOnSite: true,
    sortOrder: 20,
    metaTitle: 'Elm xəbərləri | alt404',
    metaDescription: 'Elm, araşdırma və texnologiyanın elmi tərəfinə dair yeniliklər. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'suni-intellekt',
    label: 'Süni İntellekt',
    showOnSite: true,
    sortOrder: 30,
    metaTitle: 'Süni intellekt xəbərləri | alt404',
    metaDescription: 'Süni intellekt alqoritmləri, modellər və tətbiqlər barədə xəbərlər. alt404.az',
    curatedTags: ['AI'],
  },
  {
    slug: 'startap',
    label: 'Startap',
    showOnSite: true,
    sortOrder: 40,
    metaTitle: 'Startap xəbərləri | alt404',
    metaDescription: 'Yeni startaplar, investisiyalar və məhsul yenilikləri. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'texnobloq',
    label: 'Texnobloq',
    showOnSite: true,
    sortOrder: 50,
    metaTitle: 'Texnobloq | alt404',
    metaDescription: 'Texnologiya üzrə analiz və icmal məqalələri. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'avtomobil',
    label: 'Avtomobil',
    showOnSite: true,
    sortOrder: 60,
    metaTitle: 'Avtomobil xəbərləri | alt404',
    metaDescription: 'Elektromobillər, avtomobil texnologiyası və sənaye yenilikləri. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'texnoicmal',
    label: 'Texnoicmal',
    showOnSite: true,
    sortOrder: 65,
    metaTitle: 'Texnoicmal | alt404',
    metaDescription: 'Cihazlar və texnologiya üzrə icmallar. alt404.az',
    curatedTags: [],
  },
  {
    slug: 'meqaleler',
    label: 'Məqalələr',
    showOnSite: true,
    sortOrder: 70,
    metaTitle: 'Məqalələr | alt404',
    metaDescription: 'Texnologiya və elm mövzularında məqalələr. alt404.az',
    curatedTags: [],
  },

  // Keçmiş slugs (mövcud məqalələr üçün) — saytda avtomatik görünməsin.
  {
    slug: 'cihazlar',
    label: 'Cihazlar',
    showOnSite: false,
    sortOrder: 5,
    metaTitle: 'Cihazlar | alt404',
    metaDescription: 'Smartfonlar və digər cihaz yenilikləri. alt404.az',
    curatedTags: [],
  },
];

export function getCategoryLabel(categories: CategoryConfig[], slug: string) {
  return categories.find((c) => c.slug === slug)?.label || slug;
}

export function getVisibleCategories(categories: CategoryConfig[]) {
  return [...categories]
    .filter((c) => c.showOnSite)
    .sort((a, b) => a.sortOrder - b.sortOrder);
}

