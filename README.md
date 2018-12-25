# Hearthstone-Deck

利用Python和PHP实现的炉石传说卡组解析应用。

## 使用方法

```bash
git clone https://github.com/stevenjoezhang/Hearthstone-Deck.git
#克隆这个项目
cd Hearthstone-Deck
pip install -r requirements.txt
#or pip3 install -r requirements.txt
```

首先建立MySQL数据库，并修改`deck.py`中的相关内容，使Python能够正确访问数据库。

从[HearthstoneJSON](https://hearthstonejson.com)上下载最新的`cards.collectible.json`，替换掉本项目下的同名文件。

执行`deck.py`，会自动将json文件中的有用信息导入数据库。

将`index.php`移到Web服务器（需支持PHP）的目录下，通过浏览器访问`index.php`即可查看效果。

## 鸣谢

本项目受到了[mashirozx/Awesome-Deck](https://github.com/mashirozx/Awesome-Deck)的启发。
