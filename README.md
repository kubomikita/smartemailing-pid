<br/>
<p align="center">
  <h3 align="center">Smartemailing PID API</h3>

</p>



## Table Of Contents

* [Built With](#built-with)
* [Getting Started](#getting-started)
  * [Installation](#installation)
* [Usage](#usage)

## Built With

Nette framework v3.2, PHP >= 8.1, MariaDB 10.6

## Getting Started


To get a local copy up and running follow these simple example steps.

### Installation

This is an example of how to list things you need to use the software and how to install them using composer.


```sh
composer create-project --ignore-platform-reqs kubomikita/smartemailing-pid /path-to-your-project-directory
```

**Create database** from .sql file stored in:
```
/path-to-your-project-directory/database/create.sql
```

**Setup credentials** to connect to database in:
```
/path-to-your-project-directory/config/local.neon
```

Run app in your prefered browser.

## Usage
First sync data with Open data PID with PHP-CLI
```sh
php /path-to-your-project-directory/www/index.php Cron:SyncPid
```

API endpoint for list all PID points

```GET http://localhost/path-to-your-project-directory/www/```

API endpoint for list all opened PID points

```GET http://localhost/path-to-your-project-directory/www/?isOpen=1```

API endpoint for list all opened PID points in specific date and time

```GET http://localhost/path-to-your-project-directory/www/?isOpen=1&dateTime=2023-11-17T15:40```

Query parameter **dateTime** can be integer representing UNIX TIMESTAMP

If given **dateTime is not valid**, endpoint uses **actual date and time**.

