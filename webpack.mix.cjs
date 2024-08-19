let mix = require('laravel-mix');

mix.setPublicPath('public');

// Compiler les fichiers CSS et JS pour le frontoffice
mix.js('resources/js/frontoffice/app.js', 'public/js/frontoffice')
   .css('resources/css/frontoffice/app.css', 'public/css/frontoffice');

// Compiler les fichiers CSS et JS pour le backoffice
mix.js('resources/js/backoffice/app,.js', 'public/js/backoffice/app.js')
    .css('resources/css/backoffice/app.css', 'public/css/backoffice/');
//Compilation des dependances pour le dashborard


// Copier les fichiers de node_modules vers le répertoire public
mix.copy('node_modules/bootstrap/dist/js/bootstrap.min.js', 'public/js/backoffice/bootstrap.min.js')
   .copy('node_modules/popper.js/dist/umd/popper.min.js', 'public/js/backoffice/popper.min.js')
   .copy('node_modules/smooth-scrollbar/dist/smooth-scrollbar.js', 'public/js/backoffice/smooth-scrollbar.min.js')
   .copy('node_modules/perfect-scrollbar/dist/perfect-scrollbar.js', 'public/js/backoffice/perfect-scrollbar.min.js')

mix.copyDirectory('resources/scss/backoffice/argon-dashboard','public/scss/backoffice');
// Copier les images pour le frontoffice
mix.copyDirectory('resources/images/frontoffice', 'public/images/frontoffice');

// Copier les images pour le backoffice
mix.copyDirectory('resources/images/backoffice', 'public/images/backoffice');

mix.copyDirectory('resources/fonts/backoffice', 'public/fonts/backoffice');

// Configuration de versioning pour le cache busting
if (mix.inProduction()) {
    mix.version();
}
