let mix = require('laravel-mix');

mix.setPublicPath('public');

// Compiler les fichiers CSS et JS pour le frontoffice
mix.js('resources/js/frontoffice/app.js', 'public/js/frontoffice')
   .css('resources/css/frontoffice/app.css', 'public/css/frontoffice');

// Compiler les fichiers CSS et JS pour le backoffice
mix.js('resources/js/backoffice/app.js', 'public/js/backoffice')
   .css('resources/css/backoffice/app.css', 'public/css/backoffice');

// Copier les images pour le frontoffice
mix.copyDirectory('resources/images/frontoffice', 'public/images/frontoffice');

// Copier les images pour le backoffice
mix.copyDirectory('resources/images/backoffice', 'public/images/backoffice');

mix.copyDirectory('resources/fonts/backoffice', 'public/fonts/backoffice');

// Configuration de versioning pour le cache busting
if (mix.inProduction()) {
    mix.version();
}
