import os
import time

# Запуск приложения
app_package_name = "com.shizhuang.duapp"
os.system(f"adb shell monkey -p {app_package_name} -c android.intent.category.LAUNCHER 1")

# Увеличение времени ожидания для полной загрузки приложения
time.sleep(5)  # Ожидание загрузки приложения (можете изменить по необходимости)

# Дамп UI и копирование файла
os.system("adb shell uiautomator dump /sdcard/uidump.xml")
os.system("adb pull /sdcard/uidump.xml .")

# Запись содержимого XML-файла в output.xml
with open("uidump.xml", "r") as input_file:
    content = input_file.read()

with open("output.xml", "w") as output_file:
    output_file.write(content)  # Запись в файл output.xml
