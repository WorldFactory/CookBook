# CookBook
Composer plugin to execute library recipes.

CAUTION !! This libary is in alpha version !!

Add 'recipe.json' file in library root directory :

```
{
  "actions": [
    {
      "type": "copy-file",
      "source": "recipe/file-1.md",
      "target": "file-1.md"
    },
    {
      "type": "create-folder",
      "target": "new-folder"
    },
    {
      "type": "copy-file",
      "source": "recipe/file-2.md",
      "target": "new-folder/file-2.md"
    },
    {
      "type": "copy-file",
      "source": "recipe/file-3.md",
      "target": "new-folder/file-3.md"
    },
    {
      "type": "chmod-file",
      "target": "new-folder/file-3.md",
      "mode": 775
    }
  ]
}
```
 More recipe type comming soon...