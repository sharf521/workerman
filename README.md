workerman-chat


    server {
        listen       8000;
        server_name  chat1.test.cn;
        root   D:/workerman/public;
        location / {    
            index  index.php index.html index.htm;          
             if (!-e $request_filename) {
                rewrite ^/(.*)$ /index.php/$1 last;
            }            
        }     
        
        location /pictureApi/ {
                    proxy_pass http://picture.test.cn:8080/home.php/;
                }
                location /IM_URL/ {
                    proxy_pass http://chat1.test.cn:8000/;
                }
        
                location /imApi/ {
                    proxy_pass http://chat1.test.cn:8000/imApi/;
                }
        location ~ \.php  {
            fastcgi_pass   192.168.0.25:9010;            
            fastcgi_index  index.php;
            fastcgi_split_path_info    ^(.+\.php)(/.+)$; 
            fastcgi_param PATH_INFO    $fastcgi_path_info;     
            
            fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info; 
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; 
            include        fastcgi_params;
        }
        location ~ /\.ht {
            deny  all;
        }
    }
