export function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('az-AZ', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date);
}

export function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/ə/g, 'e')
    .replace(/ö/g, 'o')
    .replace(/ü/g, 'u')
    .replace(/ğ/g, 'g')
    .replace(/ı/g, 'i')
    .replace(/ş/g, 's')
    .replace(/ç/g, 'c')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim();
}

export function estimateReadingTime(content: string): number {
  const words = content.replace(/<[^>]*>/g, '').split(/\s+/).length;
  return Math.max(1, Math.ceil(words / 200));
}

export function calculateSeoScore(title: string, content: string, tags: string[]): {
  score: number;
  tips: string[];
} {
  const tips: string[] = [];
  let score = 0;
  const plainContent = content.replace(/<[^>]*>/g, '');

  if (title.length >= 30 && title.length <= 70) {
    score += 25;
  } else {
    tips.push(title.length < 30 ? 'Başlıq çox qısadır (ideal: 30-70 simvol)' : 'Başlıq çox uzundur (ideal: 30-70 simvol)');
  }

  if (plainContent.length >= 300) {
    score += 25;
  } else {
    tips.push('Mətn çox qısadır (minimum 300 söz tövsiyə olunur)');
  }

  if (tags.length >= 3) {
    score += 25;
  } else {
    tips.push('Ən azı 3 hashtag əlavə edin');
  }

  const titleWords = title.toLowerCase().split(/\s+/).filter(w => w.length > 4);
  const contentHasKeywords = titleWords.some(word =>
    plainContent.toLowerCase().includes(word)
  );
  if (contentHasKeywords) {
    score += 25;
  } else {
    tips.push('Başlıqdakı açar sözlər mətn içindən tapılmadı');
  }

  if (tips.length === 0) tips.push('Əla! SEO göstəriciləri yaxşıdır.');

  return { score, tips };
}
