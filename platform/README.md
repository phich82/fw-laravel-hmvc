
# Structure of folder
platfom

   /core

   /plugins

   /thems

- core: chứa tất cả các modules không thể thiếu để có thể khởi chạy ứng dụng.

- plugins: chứa các modules được thực thi dưới dạng trình cắm. Khi chúng ta xóa bỏ hoặc vô hiệu hóa chúng ra khỏi composer.json thì chúng sẽ không được khởi chạy, nhưng cũng không ảnh hưởng đến hệ thống hiện tại.

- themes: chứa danh sách các themes của hệ thống. Một thời điểm chỉ nên có một theme được kích hoạt. Theme sẽ là phần sử dụng các modules core và plugins để hiển thị dữ liệu và thao tác với người dùng.

# The 'repositories' key {root_folder}/composer.json

```
"repositories": [
    {
        "type": "path",
        "url": "./platform/core/*"
    },
    {
        "type": "path",
        "url": "./platform/plugins/*"
    },
    {
        "type": "path",
        "url": "./platform/themes/*"
    }
]
```
- Đoạn khai báo này giúp composer biết được nơi tìm kiếm các custom module của bạn

# Register provider for module
- Open config/app.php
- Find to 'providers' key, insert class name of provider of module:
  TestModule\Providers\ModuleServiceProvider:class
- Or auto-register:
- Open ./platform/plugins/test-module/composer.json
  ```
   {
        ...,
        "extra": {
            "laravel": {
                "providers": [
                    "TestModule\\Providers\\ModuleServiceProvider"
                ]
            }
        },
    }
  ```
- Run command for autoload:
  ```
    composer require plugins/test-module:*
  ```

# Declare modules for starting application
- Tất cả các `core` modules sẽ được require bởi module core/base, composer.json ở thư mục gốc chỉ cần require core/base là đủ.
- In `{root}/composer.json` file, import as bellow:
  ```
  "require": {
    "core/base": "*"
  }
  ```
- Bạn cũng cần đảm bảo việc các plugins được load sau `core` modules bằng cách require core/base ở các composer.json của plugins.
- In `plugins/{module}/composer.json` file, import `core` modules as bellow:
  ```
  "require": {
    "core/base": "*"
  }
  ```

# When we change namespace of a module, we should run the following command
`composer update`
