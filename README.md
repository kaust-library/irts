# Institutional Research Tracking Service (IRTS)

Institutional Research Tracking Service (IRTS) provides a way for institutions to track publications and other research outputs affiliated to the institution and add records about them to their institutional repository. It is designed for use in conjunction with a [DSpace](https://duraspace.org/dspace/) repository (currently supporting [DSpace](https://duraspace.org/dspace/) versions 5 and 6).

## Prerequisites

IRTS has been tested on [Ubuntu](https://ubuntu.com/download/server) using PHP 7 and a MySQL 5.7.27 database. It is also recommended to install [phpmyadmin](https://www.phpmyadmin.net/downloads/) to interact with the database. The database structure and some default data is available in the [irts.sql](irts.sql) file.

To harvest metadata from your DSpace repository and from external sources, [crontab](https://crontab.guru/) tasks need to be set up separately. Some of the harvest and update scripts also accept optional parameters, for which you will need [php-cgi](https://www.howtoinstall.co/en/ubuntu/xenial/php-cgi).

## Configuration

The local setup is managed through the files in the config directory. 

```commonlisp
config/constants.php
```

[constants.php](config/constants.php) contains the local repository information as well as defining the endpoints for external metadata sources.

```commonlisp
config/credentials.php
```

[credentials.php](config/credentials.php) holds the authorization information for the local repository user, as well as the API keys or other credentials for accessing external metadata sources.

```commonlisp
config/database.php
```

[database.php](config/database.php) establishes the connection to the local MySQL database.

## Interfaces

The public directory contains several user access points. **Your web server should be set up to point and provide access only to the public directory**.

### Item Review Form

```commonlisp
public/forms/reviewCenter.php
```

[reviewCenter.php](public/forms/reviewCenter.php) is the primary user interface. It allows authorized processors to see a summary of new publications needing review and to process them.

### Reporting Dashboards

```commonlisp
public/dashboards/openAccess.php
```

[openAccess.php](public/dashboards/openAccess.php) provides a dashboard showing the level of full text file deposit.

```commonlisp
public/dashboards/irtsAdmin.php
```

[irtsAdmin.php](public/dashboards/irtsAdmin.php) provides a dashboard showing information about how quickly items are being processed over time, as well comparative information about different metadata sources.

## Institutional Branding

For the logo, please insert your institutional logo as a replacement for the placeholder logo.png file in the [images](images/) folder :

```commonlisp
public/images
```

## Built With

* PHP 7 - Primary coding language
* [phpmyadmin](https://www.phpmyadmin.net/downloads/) - Database Management Application
* [Bootstrap](https://getbootstrap.com/) - CSS Framework
* [Jquery](https://jquery.com/) - JavaScript Library 
* [Chart.js](https://www.chartjs.org/) - JavaScript Chart Framework <br/><br/>