# 使用 PHP 官方映像作為基礎
FROM php:8.2-apache

# 安裝 Python 和必要的套件
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# 設定工作目錄
WORKDIR /var/www/html

# 複製專案文件
COPY . /var/www/html/

# 安裝 Python 依賴（如果有 requirements.txt）
COPY requirements.txt .
RUN pip3 install -r requirements.txt

# 設定 Apache 配置
RUN a2enmod rewrite

# 設定適當的權限
RUN chown -R www-data:www-data /var/www/html

# 暴露端口
EXPOSE 80

# 啟動 Apache
CMD ["apache2-foreground"]
