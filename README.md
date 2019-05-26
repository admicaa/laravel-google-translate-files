# laravel-google-translate-files

laravel-google-translate-files, is a laravel package that enable you to translate all of your resources/lang (files) via google translations.

> this may help you if you work on non-native-content and you don't want
> to visit google translate every time you want to translate a word.

##  Dependencies


 - stichoza/google-translate-php
# Installing
```
composer require admica/laravel-google-translate-files
```

# usage

    php artisan files:translate {main-language} {translation-language}
  
## todo

 1. validate languages input (main-language and translation-language)
 2. making path property to allow user to choose a path to translate from 
 3. adding except property 
 4. adding other translation methods ( like google translation api ) or azure translation 
 5. finding other ideas to put in todo list.
  
