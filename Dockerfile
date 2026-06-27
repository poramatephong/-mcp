# ใช้ base image เป็น PHP พร้อม Apache
FROM php:8.2-apache

# อัปเดตแพ็กเกจระบบและติดตั้ง Zip extension (จำเป็นสำหรับ Google Client Library)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# ติดตั้ง Composer สำหรับดึงความเชื่อมโยง API หลังบ้าน
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# คัดลอกโค้ดโปรเจกต์ทั้งหมดเข้าไปในโฟลเดอร์ของ Apache
COPY . /var/www/html/

# เปิดสิทธิ์เข้าถึงไฟล์ให้ Apache
RUN chown -R www-data:www-data /var/www/html

# ตั้งค่าพอร์ตบริการ (Render จะใช้พอร์ต 80 หรือ 10000 ก็ได้ตามที่คลาวด์จัดการเส้นทางให้)
EXPOSE 80
