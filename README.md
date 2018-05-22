##**如果你要对图片进行压缩处理你可以按照如下步骤**

``` php
//实例化image(如果是验证码操作不需要传入图片路径)
$img = new Image('../temp/03.jpeg');
$img->compress(800,500,3)->save('./');
//第二个参数是type属性 1表示强制压缩图片至需要的尺寸 , 2表示等比压缩 但是只会根据宽度等比 , 3表示等比宽高压缩 空的部分会用白色填充
echo $img->getImageName();  //e24bc2d37d9c34e231b3843d97cfd8142606111a.JPG

```
上面的步骤会帮你把图片压缩至800 x 500 并且是等比压缩 , 同时保存到当前目录 , 同时帮你取名
只需要调取getImageName就能获取图片的名称
如果你是想展示而不想保存的话可以这样使用
```php
$img->compress(800,500,3)->show();
```
##马赛克
```php
$img->mask('mask test' , 1)->show();
//第二个参数是type类型 1表示文字马赛克 2表示图片马赛克(需要用户提供图片)
```
save用法
```php
$img->mask('mask test' , 1)->save('./');
```

##验证码
```php
$img->authCode()->show();
```
验证码配置
```php
//例子
$img->authCode([
    'width' => 500,
    'height' => 500,
    'size' => 40,
])->show();
```