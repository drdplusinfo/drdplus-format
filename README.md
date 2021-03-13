# Pravidla DrD+ z PDF do HTML

Když jsem převáděl pravidla z PDF na [web](https://www.drdplus.info/), tak jsem ručně kopíroval text z PDF, vkládal do textového souboru a poté ručně přidával odstavce, nadpisy, odstraňoval rozdě-lovače, obaloval příběh tagy, aby byl *formátovaný*, ručně dával strukturu tabulkám a tisíce dalších úprav stylu.

Přitom jsem si nemohl nevšimnout, že se hodně stylů opakuje a že by to snad šlo aspoň částečně detekovat a zformátovat automaticky.

A na tohle jsou tyhle skripty.

Stačí pustit PHP v adresáři s těmihle soubory

```
php -S localhost:9999
```

a pak otevřít v prohlížeči tu adresu http://localhost:9999/ (kdyby ti port kolidoval s jinou aplikací, tak prostě použij jiný).
