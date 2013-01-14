# Set the deployment directory on the target hosts.
set :deploy_to, "/home/krondaco/public_html/dev.kronda.com"

# The hostnames to deploy to.
role :web, "kronda.com"

# Specify one of the web servers to use for database backups or updates.
# This server should also be running Wordpress.
role :db, "kronda.com", :primary => true

# The path to wp-cli
set :wp, "cd #{current_path}/#{app_root} ; /usr/bin/wp"

# The username on the target system, if different from your local username
ssh_options[:user] = 'krondaco'