# SPEK WordPress Theme

Production source for the custom WordPress theme used by SPEK.

## Repository scope

This repository contains the **custom theme only**. It does not contain the WordPress core, database, Media Library uploads, runtime cache, or server configuration.

The theme files are intended to be deployed to:

`public_html/wp-content/themes/spek-theme/`

## Branches

- `main` — production / live branch
- `dev` — development branch

Recommended workflow:

1. Make changes on `dev`.
2. Test the changes.
3. Merge `dev` into `main`.
4. Hostinger deploys the `main` branch to the live theme directory.

## Deployment

Hostinger should be connected to this repository with:

- Repository: `ilumadigital/spek`
- Production branch: `main`
- Target directory: `public_html/wp-content/themes/spek-theme/`

Do not deploy the repository into `public_html/` itself.

## Managed by

ILUMA Digital Agency
