# Not Maintained

本仓库不再维护，请转到[hsdeck](https://github.com/stevenjoezhang/hsdeck)。

# Hearthstone Deck

利用Python和PHP实现的炉石传说卡组解析应用。请使用Python3运行。

## 使用方法

```bash
# Clone this repository
git clone https://github.com/stevenjoezhang/Hearthstone-Deck.git
# Go into the repository
cd Hearthstone-Deck
# Install dependencies
pip3 install -r requirements.txt
```

首先建立MySQL数据库，并修改`deck.py`中的相关内容，使Python能够正确访问数据库。  
从[HearthstoneJSON](https://hearthstonejson.com/docs/cards.html)上找到最新的`cards.collectible.json`，替换掉本项目下的同名文件。  
如果你直接点击这个网页上的链接，会跳转到一个类似于  
https://api.hearthstonejson.com/v1/30103/enUS/cards.collectible.json  
的网址。这里只有英文版本的卡牌描述。如果需要多语言版本，需要手动修改为：  
https://api.hearthstonejson.com/v1/30103/all/cards.collectible.json  
然后下载该文件并替换即可。

执行`deck.py`，会自动将json文件中的有用信息导入数据库。  
将`public`目录移到Web服务器（需支持PHP）的目录下，通过浏览器访问`index.php`即可查看效果。

## 鸣谢

本项目受到了[mashirozx/Awesome-Deck](https://github.com/mashirozx/Awesome-Deck)的启发。

## License
Released under the GNU General Public License v3  
http://www.gnu.org/licenses/gpl-3.0.html
