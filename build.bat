:: Delete old data
del orfmediathek.host

:: get recent version of the provider base class
copy /Y ..\provider-boilerplate\src\provider.php provider.php

:: create the .tar.gz
7z a -ttar -so orfmediathek INFO orfmediathek.php provider.php | 7z a -si -tgzip orfmediathek.host

del provider.php