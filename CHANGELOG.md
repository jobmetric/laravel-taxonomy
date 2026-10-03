# Changelog

## 4.2.2

- Scope translated name uniqueness to siblings, including an explicit NULL parent for roots.
- Preserve the current parent scope when partial updates omit parent_id, and apply the same scope to translation edits.

## 4.2.1

- Resolve class-injected and facade taxonomy registries to the same singleton, including canonical URL generation.

## 4.2.0

- Add `urlPrefix('catalog/categories')` and `getUrlPrefix()` to taxonomy type builders.
- Prefix canonical hierarchical URLs once, while keeping stored slugs unchanged.
- Accept the same validated `url-prefix` option in array/config registration. Changing a prefix requires rebuilding existing URLs with `Taxonomy::rebuildAllUrls()`; no migration is run automatically.

## 4.1.8

- Fix storing/updating taxonomy slugs by using URL 3.x's `dispatchSlug` API.
- Verified with the Jobic gallery/URL integration regression test; no schema change.
