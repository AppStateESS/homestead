homestead
=========
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AppStateESS/homestead/badges/quality-score.png?s=d4e5a31be92390a264c73c4282dd8cfb9c36400b)](https://scrutinizer-ci.com/g/AppStateESS/homestead/)

On-campus Housing Management

# Mr. Developer:

**To you, who has been tasked with finishing this bootstrap, here are some tips:**

 * The main template file is `templates/hms.tpl` *duh*

 * I think you will need to bootstrap all 10 million template files. I made a menu and bootstrapped the first page.

 * You get to go through and find all of the unused CSS. What luck!





### Changes I made that may need another look

 * The `templates/user.tpl` and `templates/guest.tpl` are now identical. Could find a way to remove one. The menubar handles the differences in priveleges.

 * I created a `class/CommandMenuMenubar.php` and `templates/CommandMenuMenubar` which manage the dropdowns, I don't think dropping the other will break anything

 * Recent searches has nested dropdowns. I don't think they will play nicely on iPhone, etc. so you will to to work some css magic for that.

 * The footer is like 10-20px above the bottom of the page. I can't for the life of me figure out where the style causing that is coming from. Not a biggie, just annoying as F***.