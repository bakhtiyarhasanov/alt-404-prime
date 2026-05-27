import { useLayoutEffect } from 'react';

function upsertMeta(opts: { name?: string; property?: string; content: string }) {
  const selector = opts.property
    ? `meta[property="${opts.property}"]`
    : `meta[name="${opts.name}"]`;

  let el = document.querySelector(selector) as HTMLMetaElement | null;
  if (!el) {
    el = document.createElement('meta');
    if (opts.property) el.setAttribute('property', opts.property);
    if (opts.name) el.setAttribute('name', opts.name);
    document.head.appendChild(el);
  }
  el.content = opts.content;
}

function upsertLink(opts: { rel: string; href: string }) {
  let el = document.querySelector(`link[rel="${opts.rel}"]`) as HTMLLinkElement | null;
  if (!el) {
    el = document.createElement('link');
    el.setAttribute('rel', opts.rel);
    document.head.appendChild(el);
  }
  el.href = opts.href;
}

export default function SeoHead({
  title,
  description,
  canonical,
}: {
  title: string;
  description?: string;
  canonical?: string;
}) {
  useLayoutEffect(() => {
    if (title) document.title = title;
    if (description) upsertMeta({ name: 'description', content: description });

    // Basic OpenGraph sync (for social previews)
    upsertMeta({ property: 'og:title', content: title });
    if (description) upsertMeta({ property: 'og:description', content: description });

    if (canonical) upsertLink({ rel: 'canonical', href: canonical });
  }, [title, description, canonical]);

  return null;
}

