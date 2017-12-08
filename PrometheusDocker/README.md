# Prometheus configuration

#### Table of Contents
1. [Description - What does this configuration and why it is useful](#description)
2. [Setup - The basics of getting started with prometheus](#setup)
    * [What Prometheus affects](#what-prometheus-affects)
    * [Setup requirements](#setup-requirements)
    * [Setup SSL Certificate](#setup-ssl-certificate)
    * [Configuring Prometheus](#configuring-prometheus)
    * [Configuring Grafana](#configuring-grafana)
3. [Reference](#reference)
4. [Limitations](#limitations)


## Description

If you do not have a central Prometheus server, you can follow this guide, to setup a local Prometheus on your
webserver for displaying metrics of your TYPO3 installation and the underlying server system. You can also use
this configuration to setup a central prometheus server for monitoring multiple TYPO3 instances. 

Inside this directory, you'll find all the needed configurations to run a local working Prometheus server, 
a Grafana dashboard, an exporter for your system metrics and a proxy with SSL support. 
 
The setup should'nt take longer than a couple of minutes. All you need is an installed and running docker daemon,
shell access to the target system and a SSL certificate. 
    
After setting all this up, you can integrate the dashboard seamlessly into your TYPO3 Backend with the Prometheus 
backend module.

## Setup

### What prometheus affects

If you monitor only one TYPO3 instance, the resource allocation of Prometheus should be low. We do not expect that
there will be much cpu, memory and disk space or i/o resources needed. When you want to monitor multiple TYPO3
instances, the resource usage depends on the instance count - then you should carefully inspect the resource usage 
and possibly consider to run Prometheus on dedicated machine.

All services will use non-standard ports, so there will be no conflicts with your webserver, database or any other
networking service. This Setup does not require root privileges, a user with access to the docker daemon is sufficient.

### Setup Requirements

You will need at least:

- docker daemon >= 1.13.0
- docker compose >= 1.10
- shell access 
- access to docker daemon
- a SSL certificate (self-signed should be sufficient for testing, )

We'll recommend to use the latest stable software versions of docker and docker compose.

### Setup SSL Certificate
If you don't have any SSL certificates installed for your TYPO3 backend, you should really get some now!

If you already own a SSL certificate for your TYPO3 backend, and you are planning to run the Prometheus not on a 
separate machine, you can reuse this certificate. Then you should set the hostname for the dashboard in the following 
configuration to the same as your TYPO3 backend. 

If you do not already have a SSL certificate for the hostname of the dashboard, you can generate a certificate by 
yourself. For production use we'll recommend to have an officially signed certificate (i.e from letsencrypt):

`~ $openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365`

### Configuring Prometheus

Since you have to edit the configuration files for prometheus and for the docker swarm stack, we'll recommend to copy
this directory (`PrometheusDocker`) to a location outside of TYPO3.

Before running the setup you have to have the following information:
- Location of your SSL certificate
- Hostname to use for accessing the metrics dashboard
- Hostname of the TYPO3 installation (should be the same, except you are running Prometheus on a separate machine)

In the following guide we'll assume that you copied the configuration to  `/opt/PrometheusDocker`, when you chose any
other path, just modify the following configuration accordingly. 

First you have to configure the SSL Certificates, which you've already setup above. It is important, that the names for 
the certificates match the hostname, so the proxy can automatically include the certificates:

`~ $ mkdir /opt/PrometheusDocker/ssl`

`~ $ cp ssl_certificate.www.example.de.cert /opt/PrometheusDocker/ssl/www.example.de.crt`

`~ $ cp ssl_certificate.www.example.de.key /opt/PrometheusDocker/ssl/www.example.de.key`


By editing the file `docker-compose.yml` you configure the proxy service, which is responsible for SSL termination and
password protected access to the metrics dashboard:

Setup the hostname which you will use for the dashboard access to `www.example.org`: 
```
grafana:
  .
  .
  environment:
    - 'VIRTUAL_HOST=www.example.org'
```

Also you want to setup an admin password for Grafana. You need to change the following line:
```
grafana:
  .
  .
  environment:
    .
    .
    - 'GF_SECURITY_ADMIN_PASSWORD=foobar'
```


Setup the paths to your ssl certificates you've configured above: 
```
proxy:
  .
  .
  volumes: 
    - /opt/PrometheusDocker/ssl:/etc/nginx/certs
```


Then you'll setup the scrape config for prometheus, so you'll get some metrics from our TYPO3 installation, by editing 
`/opt/PrometheusDocker/prometheus/prometheus.yml`:

```
scrape_configs:
  - job_name: 'typo3'
    .
    .
    static_configs:
         - targets: [ 'www.example.org' ]
```

Now you can start our docker swarm stack by issuing the following commands on our commandline from inside our 
configuration directory `/opt/PrometheusDocker`:

`~ $ docker swarm init`

`~ $ docker stack deploy -c docker-compose.yml prometheus`

Now it should only take a couple of seconds till you have access to the Grafana Frontend via browser at
`https://www.example.org:4433`. Once you've opened the page and entered your credentials you've generated above, you can 
finish the configuration by setting up a datasource for the Grafana Frontend and adding our TYPO3 dashboard for 
displaying the metrics.  

### Configuring Grafana

To start the configuration, you need to login to Grafana. You can open the login page with the following url: 
`https://www.example.org:4433/login` and login with the user `admin` and the password you configured above:

![Login](img/01_login.png?raw=true "Login")

Then you should see the following screen:

![Configuration overview](/PrometheusDocker/img/02_config_overview.png?raw=true "Configuration overview")

After clicking on `Add data source`, you can enter the address of our prometheus installation, you should use the 
following configuration values:

Name: `prometheus`
Type: `Prometheus`
URL: `http://prometheus:9090`
Access: `proxy`

![Add datasource](/PrometheusDocker/img/03_add_datasource.png?raw=true "Add datasource")

After clicking on `add` you should see the following success message:

![Add datasource success](/PrometheusDocker/img/04_add_datasource_success.png?raw=true "Add datasource success")

Now you can import the TYPO3 dashboard, which is delivered with this extension in the file `TYPO3_dashboard.json`. Just 
open the file, and copy the json content to your clipboard. Then go to `Dashboard > Import`:

![Goto_import_dashboard](/PrometheusDocker/img/05_goto_import_dashboard.png?raw=true "Goto import dashboard")

Then paste the json content into the appropriate field:

![Import_dashboard](/PrometheusDocker/img/06_import_dashboard.png?raw=true "Import dashboard")

Setup the name for the dashboard, and finish importing:

![Finish import_dashboard](/PrometheusDocker/img/07_finish_import_dashboard.png?raw=true "Finish import dashboard")

Then you should see the Dashboard for your TYPO3 installation:

![Dashboard](/PrometheusDocker/img/08_dashboard.png?raw=true "Dashboard")

### Configuring TYPO3

Now you can enter the dashboard URL in the extension configuration, and activate the TYPO3 backend module:

![Setup TYPO3](/PrometheusDocker/img/09_setup_typo3.png?raw=true "Setup TYPO3")

Now you should see the module in the system category:

![Open module](/PrometheusDocker/img/10_open_module.png?raw=true "Open module")
 
### Reference

#### Docker
Home: https://docs.docker.com/

#### Prometheus
Home: https://prometheus.io/

Docker Image: https://hub.docker.com/r/prom/prometheus/

#### Grafana
Home: https://grafana.com/

Docker Image: https://hub.docker.com/r/grafana/grafana/

#### Nginx
Home: https://nginx.org/en/

Docker Image: https://hub.docker.com/r/jwilder/nginx-proxy/

## Limitations

Currently there are no known limitations.