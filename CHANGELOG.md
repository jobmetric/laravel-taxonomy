# Changelog

## 4.2.0

- Add `urlPrefix('catalog/categories')` and `getUrlPrefix()` to taxonomy type builders.
- Prefix canonical hierarchical URLs once, while keeping stored slugs unchanged.
- Accept the same validated `url-prefix` option in array/config registration. Changing a prefix requires rebuilding existing URLs with `Taxonomy::rebuildAllUrls()`; no migration is run automatically.

## 4.1.8

- Fix storing/updating taxonomy slugs by using URL 3.x's `dispatchSlug` API.
- Verified with the Jobic gallery/URL integration regression test; no schema change.
