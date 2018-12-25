#!/usr/bin/env python3
import json, pymysql

if __name__ == "__main__":

	table_name = "hearthstone"

	with open("cards.collectible.json", 'r') as f:
		cards = json.load(f)

	conn = pymysql.connect(host = "127.0.0.1", user = "username", password = "password", db = "hearthstone", charset = "utf8")
	print(conn)
	cur = conn.cursor()

	attrs = ["dbfId", "id", "cardClass", "cost", "rarity"]
	langs = ["enUS", "zhCN"]
	maxlength = [{attr: 0 for attr in attrs}, {lang: 0 for lang in langs}]
	print(maxlength)

	for card in cards:
		for attr in attrs:
			if attr in card and len(str(card[attr])) > maxlength[0][attr]:
				maxlength[0][attr] = len(str(card[attr]))
		if "name" in card:
			for name in card["name"]:
				for lang in langs:
					if len(str(card["name"][lang])) > maxlength[1][lang]:
						maxlength[1][lang] = len(str(card["name"][lang]))

	cur.execute("DROP TABLE IF EXISTS " + table_name)

	#create table
	sql_create_table = '''CREATE TABLE ''' + table_name + ''' (
			dbfId int(%s) UNSIGNED PRIMARY KEY NOT NULL,
			id varchar(%s) DEFAULT NULL,
			cardClass varchar(%s) DEFAULT NULL,
			cost int(%s) DEFAULT NULL,
			rarity varchar(%s) DEFAULT NULL,
			enUS varchar(%s) DEFAULT NULL,
			zhCN varchar(%s) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8''' % (tuple(maxlength[0][attr] for attr in attrs) + tuple(maxlength[1][lang] for lang in langs))

	cur.execute(sql_create_table)

	for card in cards:
		flag = False
		for attr in attrs[:3]: # Hero cards
			if not attr in card:
				flag = True
		if not "name" in card:
			flag = True
		if flag:
			print(card)
			continue
		else:
			data = tuple(card[attr] if attr in card else 0 for attr in attrs) + tuple(card["name"][lang] for lang in langs)
			try:
				cur.execute("insert into " + table_name + " values(%s,%s,%s,%s,%s,%s,%s)", data)
			except pymysql.err.InternalError:
				print("\033[31mERROR: Incorrect string value.\033[0m", data)
			except pymysql.err.DataError:
				print("\033[31mERROR: Data too long.\033[0m", data)
	cur.connection.commit()
	cur.close()
	conn.close()
	print(maxlength)
