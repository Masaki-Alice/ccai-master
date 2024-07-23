@servers([
    'localhost' => '127.0.0.1',
    'staging_server' => 'root@data.ccai.pawait.io',
])

@task('build_localhost', ['on' => 'localhost'])
    cd /Users/nelson/Documents/www/ccai/data-manager
    echo "START => Making production build..."
    git add .
    git commit -m "Automated deployment on $(date)"
    git push origin master
    echo "OK => Successfully built production assets..."
@endtask

@task('build_production', ['on' => 'staging_server'])
    cd /var/www/ccai/data-manager
    git pull
    {{-- composer update --}}
    chmod -R 0777 storage
    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    {{-- npm install --}}
    npm run build
@endtask

@task('notify', ['on' => 'localhost'])
    osascript -e 'display notification "Successfully deployed CCAI Data Manager!" with title "Deployment Successful!"'
@endtask

@story('deploy')
    build_localhost
    build_production
    notify
@endstory
