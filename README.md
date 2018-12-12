# CookBook
Composer plugin to execute library recipes.

CAUTION !! This libary is in alpha version !!

## How to use it

Add cookbook to the composer.json file of your library.

```composer require worldfactory/cookbook``` 

Add 'recipe.json' file in the root directory of your library :

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

All configured actions will be executed when your library is installed !! ;)

## Comming soon

 * More recipe types. (Symfony and composer hooks integration)
 * Test property to condition recipe execution.
 * Input from user.
  
 [Trello development tab.](https://trello.com/b/0YAPil3f/cookbook)