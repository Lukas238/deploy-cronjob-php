# deploy-cronjob-php 

This is a _Poor's Man Jenkins_ deploy tool.

Use this to automatically deploy a website using a git command, 1 minute after receiving a webhook from the Bitbucket repository.
## Prerequisites

- Git 2.26+
    This make use of Git property `sparse-checkout`, and need Git version 2.26 or up installed in the server.
    See https://git-scm.com/download/linux for more info on how to update Git version on linux.
- PHP
- Admin permissions to:
    - Add a Cron Job
    - Add SSH keys
- Bitbucket.uhub.biz project or repo admin permissions to add access SSH keys.
## Installation

1. Update git to last version on Ubuntu
    1. Add Git repository to ubuntu apt:
    ```sh
    add-apt-repository ppa:git-core/ppa
    ```
    2. Update apt, and install git: 
    ```sh
    apt update; apt install git
    ```
2. On you server, clone this repo or manually create a folder with the repo contents. For example `/var/www/deploy`.
3. Edit the file `/var/www/deploy/config.php` and setup the configuration to match your system and needs:
    - `date_default_timezone_set`: Is used to set the dates in the log files. See https://www.php.net/manual/en/timezones.php for more time zones strings.
    - `$secret`: Set the password string to use to validate the webhooks call as from your repositories server. Default is `1234567`.
    You will need to add this secret when configuring the webhook on Bitbucket.
    - `$defaultRepoSettings['command']`: This is the default git update command. If needed, you will be able to set a different command for each individual repo added to the `repos.conf` file.
4. Create a webserver configuration for the deploy tool and point it to the repo `/public` folder.
    - **Optional**: Associate a domain for the deploy tool, ex.: `https://deploy.myserver.com`.
5. To validate, open the deploy tool URL in a browser. You should get the following message:
    ```html
    Nothing to do.
    ```
## Setup

### CronJob

The deploy tool requires a cron job to be configured to run the `update.php` script every minute.

1. Change to the root user:
    ```sh
    sudo su
    ```
2. Edit the crontab: 
    ```sh
    crontab -e
    ```
3. Add a new job to run `deploy.php` script every minute:
    ```
    * * * * * /usr/bin/php /var/www/deploy/deploy.php
    ```
    - Replace `/usr/bin/php` with your system PHP path. You can find it using the command `whereis php`.
    - Replace `/var/www/deploy/deploy.php` with the **full path** for the `deplopy.php` script file.


### Repos Configuration

To let the deploy tool to be able to automatically update your sites, you need add each site repo name and folder path to the `/var/www/deploy/repos.conf` repo configuration file.

For this example assume that, in Bitbucket, the site repository name is `repo-test1` under the project `PORJECTNAME`.

1. Rename the file `repos.sample.conf` to  `repos.conf`. This is in JSON file format.
2. If do not exist, add your repository project name as an object, ex.: `PORJECTNAME`.
    ```json
    {
        "PORJECTNAME": {}
    }
    ```
3. Under the correct project name add your repository name as an object, ex.: `repo-test1`.
    ```json
    {
        "PORJECTNAME": {
            "repo-test1": ""
        }
    }
    ```
4. As value, paste the  **full path** to the folder where you will be cloned the website repo.
    See [Website setup](#website-setup) for more information.
    ```json
    {
        "PORJECTNAME": {
            "repo-test1": "/var/www/repo-test1"
        }
    }
    ```
5. Done.

#### Alternative repo config

The minimal repo configuration need just to include the **full path** to the server folder in which you cloned the website, to be able to run the default git update command configured on `/var/www/deploy/config.php` file.

But you may need to run a different git command, or add another command first, or after.

To do so, instead of passing a string with the **full path** of the repo, you can use the following object to set the path and a custom command.

```json
{    
    "path": "/var/www/repo-test2",
    "command": "my_custom_command && git fetch --depth 1 && git reset --hard @{upstream}"
}
```
##### Example

The following is an example of two repositories under the same project name `PORJECTNAME`, `repo-test1` with the minimal configuration , and `repo-test2` with a custom command configuration.
```json
{
    "PORJECTNAME": {
        "repo-test1": "/var/www/repo-test1",
        "repo-test2": {
            
            "path": "/var/www/repo-test2",
            "command": "my_custom_command && git fetch --depth 1 && git reset --hard @{upstream}"
        }
    }
}
```
### SSH access to Bitbucket

To be able to update a website repository, the deploy tool need SSH access to the repositories.

To do so we need to create a _deploy_ SSH key, add the private key part the root user of the server, and add the public key part to the necessary Bitbucket projects or repos.


#### Generate and add a deploy SSH key on your server

1. Change to the root user: `sudo su`.
2. To generate a new SSH key run the following command:
    ```sh
    ssh-keygen -t rsa -b 4096 -C "deploytool"
    ```
    You can change the comment to what ever make this key more identifiable to you.
3. You will be prompted to specify the file name:
    ```sh
    Enter file in which to save the key (/home/yourusername/.ssh/id_rsa):
    ```
    The default location and file name should be fine for most users. Press `Enter` to accept and continue.
4. Next, you’ll be asked to type a secure passphrase. A passphrase adds an extra layer of security. If you set a passphrase, you’ll be prompted to enter it each time you use the key to login to the remote machine.
    If you don’t want to set a passphrase, press Enter.
    ```sh
    Enter passphrase (empty for no passphrase):
    ```
5. The whole interaction looks like this:
    ```sh
    yourusername@ubuntu1804:~$ ssh-keygen -t rsa -b 4096 -C "deploytool"
    Generating public/private rsa key pair.
    Enter file in which to save the key (/home/yourusername/.ssh/id_rsa):
    Created directory '/home/yourusername/.ssh' .
    Enter passphrase (empty for no passphrase):
    Enter same passphrase again:
    Your identification has been saved in /home/yourusername/.ssh/id_rsa.
    Your public key has been saved in /home/yourusername/.ssh/id_rsa.pub.
    The key fingerprint is:
    SHA256:2zTTC82cFN19x69SJUcØOVdNDqcd5HGVy4c8t6V6mjg deploytool
    The key's randomart image is:
    +---[RSA 4096]----+
    |              +%/|
    |              +@/|
    |             .+*X|
    |           . ==+=|
    |        S + o.==+|
    |        + o.oo.  |
    |       . . .o    |
    |         E....   |
    |         ..oo    |
    +----[SHA256]-----+
    ```
6. To verify your new SSH key pair is generated, type:
    ```sh
    ls ~/.ssh/id_*
    /home/yourusername/.ssh/id_rsa /home/yourusername/.ssh/id_rsa.pub
    ```
7. That’s it. You’ve successfully generated an SSH key pair on your Ubuntu client machine

### Adding the SSH public key part to your Bitbucket project or repo

Adding the public ssh key to a project will grant access to all repositories under the project. 

1. From your server, copy the public SSH key part.
    You can use `cat` command to print the key in screen and copy it from there:
    ```sh
    cat < ~/.ssh/id_rsa.pub
    ```
2. From Bitbucket, go to the `Settings` tab for the project or repository.
3. Click `Access keys` and then `Add key`.
4. Choose the **Read** permission for the git operations, as you want to be sure that the remote system will not be able to write back to the Bitbucket repository.
5. Paste the key into the text box and click `Add key`.

    ![](https://confluence.atlassian.com/bitbucketserver/files/776639781/776639783/1/1395624592894/Stash212_add-key.png)

### Bitbucket Webhook Config

1. From Bitbucket, open the repository where you want to add the webhook.
2. Click the `Repository settings` link on the left side.
3. From the links on the Repository settings page, click the `Webhooks` link.
4. Click the `Create webhook` button to create a webhook for the repository. 
5. On the Create webhook page, enter:
    - A `Name` with a short description, ex.: `DeployTool`
    - The `URL` to the deploy tool, ex: `https://deploy.myserver.com`
    - The `Secret` to pass to the deploy tool. See [Installation](#installation) for more info.
        Optional: If you're using a self-signed certificate and want to disable certificate verification, select `Skip certificate verification`.
7. To validate, click on the `Test connection` button. You should get the response `200`.
    If you click on `View details` link a popup will open with the test request and response payload. The expected response body should be the string `Success!`.
6. If necessary, check the **Push** even field under `Events: Repository` list.
7. After you entered all the necessary information for your webhook, click `Create`.

## Website setup

On this example we assume that the site repository includes multiple folders that we don't really need to checkout on the webserver (like `/src`, `/assets`, `/docs`, `/dev`, etc), and that the compiled version of the site is on `/public` folder.

1. Connect by SSH to the server.
2. On a private folder (outside the webserver served folders), clone the repo without checking out the files yet, and as a _shallow clone_:
    ```sh
    git clone <your-repo-clone-URL> --no-checkout --depth 1
    ```
2. Enter to the newly created folder:
    ```sh
    cd <cloned-folder>
    ```
3. Initialize the _sparse-checkout_ basic settings (this will include all files only in the root of the repo):
    ```sh
    git sparse-checkout init --cone
    ```
4. Add the relative path to the folders you want to include in the checkout (space separated), `/public` folder in this example: 
    ```sh
    git sparse-checkout set public
    ```
5. Checkout the files:
    ```sh
    git checkout
    ```
6. You should now see files on the repo root and only the `/public` folder checked out.
7. Edit your webserver configuration and add a site configuration that points directly to the `/public` folder on your site repo.

### Manual Update

The cron tab job will take care to update your site automatically.
That said, if in need, you can manually update your site repo by running the following git command by yourself:

```sh
git fetch --depth 1 && git reset --hard @{upstream}
```

- The `fetch --depth 1` command will pull just the last version of the files, keeping the git history 1 level deep. This is to avoid waste server space with previous versions of the files.
- The `reset --hard @{upstream}`command will reset the files to the last version of the current branch

**Keep in mind that this will destroy any local or temp files not part of the repository.**
And while this is not an issue for a static site, you may want to change the git command to match your needs.

## Activity Log

- The deploy tool will log all update activities in daily log files with the name `deploy_[day number].log`, in the configured log folder.
- Only the last 31 days logs are kept.
- If the log file for the current day number already exist, it is deleted and recreated.

### Log format

The logs files will include a single line for each deploy executed, including the following information:
- Date and time
- Project Name
- Repo Name
- The repository full path in the server
- The command executed
- The command result number
- The command output

**Example:**
```txt
2021-07-28 11:32:03am - PROJECTNAME_repo-test1 - /var/www/repo-test1 - git fetch --depth 1 && git reset --hard @{upstream} - 0
        HEAD is now at 56d5b9e Fixed typo in the site
```
