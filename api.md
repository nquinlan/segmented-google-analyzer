FORMAT: 1A
HOST: https://sendgrid.com/blog/wp-admin

# Segmented Google Analyzer
Segmented Google Analyzer provides data about posts over their first week of life.

## Data  [/admin-ajax.php]

+ Parameters
    + action (required, string, `sendgrid_sga_data`) ... Must be `sendgrid_sga_data`
    + api_key (required, string) ... API key set through the Plugin's Backend.
    + period\_start (required, date, `2014-01-15`) ... The start of the period you wish to examine. _Formatted YYYY-MM-DD._
    + end\_start (required, date, `2014-01-31`) ... The end of the period you wish to examine. _Formatted YYYY-MM-DD._
    + data (required, string, `users`) ... The type of data you wish to get, this may either be `users` or `posts`

### User Data [POST]

+ Request (application/x-www-form-urlencoded)

        data=users&period_start=2014-01-15&period_end=2014-01-31&api_key=JeC1oT&action=sendgrid_sga_data

+ Response 200 (application/json)

        {
            "error" : false,
            "body" : [
                {
                    "raw": {
                        "post_author": {
                            "display_name": "Brandon West",
                            "name": "",
                            "first_name": "Brandon",
                            "last_name": "West"
                        },
                        "mantime": 2017179,
                        "visits": 6460,
                        "pageviews": 7056,
                        "avg_time_on_page": 285.8814,
                        "entrance_rate": 0.9277,
                        "exit_rate": 0.914
                    },
                    "formatted": {
                        "post_author": "Brandon West",
                        "mantime": "23d 8h 19m 39s",
                        "visits": "6,460",
                        "pageviews": "7,056",
                        "avg_time_on_page": "4m 45s",
                        "entrance_rate": "92.77%",
                        "exit_rate": "91.4%"
                    }
                }
            ],
            "period_start": "2014-01-15",
            "period_end": "2014-01-31"
        }

+ Request (application/x-www-form-urlencoded)

        data=posts&period_start=2014-01-15&period_end=2014-01-31&api_key=JeC1oT&action=sendgrid_sga_data

+ Response 200 (application/json)

        {
            "error" : false,
            "body" : [
                {
                    "raw": {
                        "guid": "https://sendgrid.com/blog/?p=7431",
                        "post_title": "Technical Debt is Not a Bad Thing",
                        "post_author": {
                            "display_name": "Brandon West",
                            "name": "",
                            "first_name": "Brandon",
                            "last_name": "West"
                        },
                        "mantime": 1878912,
                        "visits": 6017,
                        "pageviews": 6524,
                        "avg_time_on_page": 288,
                        "entrance_rate": 0.946,
                        "exit_rate": 0.93
                    },
                    "formatted": {
                        "guid": "https://sendgrid.com/blog/?p=7431",
                        "post_title": "Technical Debt is Not a Bad Thing",
                        "post_author": "Brandon West",
                        "mantime": "21d 17h 55m 12s",
                        "visits": "6,017",
                        "pageviews": "6,524",
                        "avg_time_on_page": "4m 48s",
                        "entrance_rate": "94.6%",
                        "exit_rate": "93%"
                    }
                }
            ],
            "period_start": "2014-01-15",
            "period_end": "2014-01-31"
        }
