# Whatpulse Team Stats

Just another team stats site ;)

Not yet completely finished, but useful!

Sample site: http://www.grandmasg.nl/WPNEW"

Script uses the full xml of the team data and the separate user data to keep them up to date.
Sample Team: http://api.whatpulse.org/team.php?team=1295&members=yes&format=xml
Sample User: http://api.whatpulse.org/user.php?user= Number (<<--looping from the teamstats)

Script runs for ~ 45-55 minutes (some delay in cronfile: sleep(2); between the connections and run it every hour.)
Team 1295 has around 1200 users.

> ### Attention: 
> * Please put the cron file outside the public_html 
> * Complete stats on the third day
Quote break.