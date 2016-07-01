rhc port-forward -a php

# Для запуском и управления монитора процессов
sudo supervisord -c /etc/supervisor/supervisord.conf
sudo supervisorctl -c /etc/supervisor/supervisord.conf
sudo unlink /var/run/supervisor.sock

#публикация ассетов для провайдера
php artisan vendor:publish --tag=public --force
