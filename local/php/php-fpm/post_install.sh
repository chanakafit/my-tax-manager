composer install --optimize-autoloader --no-interaction
php yii migrate/up --interactive=0
echo "Post-install completed. Default admin user: admin / Password: admin123"


