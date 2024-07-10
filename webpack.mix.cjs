let mix = require('laravel-mix');

mix.setPublicPath('public');

// Compiler les fichiers CSS et JS
mix.js('resources/js/frontoffice/app.js', 'public/js/frontoffice')
   .css('resources/css/frontoffice/app.css', 'public/css/frontoffice');

// Copier les images
mix.copyDirectory('resources/images', 'public/images');

// Configuration de versioning pour le cache busting
if (mix.inProduction()) {
    mix.version();
}
