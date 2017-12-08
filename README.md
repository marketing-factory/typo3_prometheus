# Prometheus extension for TYPO3

# !!! Caution !!!
### This extension is under hard development, so things can change (and brake) in master branch without warning!
### This warning will disappear on the first stable release. 
### You are welcome to give us feedback and we would be happy for any contribution. 
### Please find us at the TYPO3 Slack in #ext-prometheus.
# !!! Caution !!!


#### Table of Contents
1. [Description - What does this extension do and why it is useful](#description)
2. [Setup - The basics of getting started with prometheus](#setup)
    * [What prometheus affects](#what-prometheus-affects)
    * [Setup requirements](#setup-requirements)
    * [Beginning with prometheus](#beginning-with-prometheus)
3. [Reference](#reference)
4. [Limitations](#limitations)

## Description

This extension can be used to export the metrics for your TYPO3 installations to a Prometheus monitoring solution. It 
will give you a deeper insight on what is happening in your TYPO3 installation, by using the data and metadata which
is already present in your TYPO3 database.

It also provides you an easy step-by-step guide, on how to setup a simple prometheus monitoring, and an integration into
your TYPO3 Backend.

## Setup

### What prometheus affects

This extension provides metric data for <a href="https://prometheus.io">prometheus.io</a>. All data which is raised by 
this extension is already present as data or metadata in the TYPO3 database. We just collect all the data and create an
output which Prometheus is able to scrape. 

We also provide the possibility to integrate a dashboard to view the data via grafana inside the TYPO3 Backend. You can
find a configuration guide on how to setup prometheus in <a href="/PrometheusDocker/README.md">this Document</a>.   

The extension differentiates between three types of data:
- fast changing metrics
- medium changing metrics
- slow changing metrics

The metrics are generated asynchronously to the output of the metrics via extbase commands. One for every metric 
type, so you have the possibility to fit the interval for generating data to your needs. The scraping of the metrics is
a basic select command, so you don't need to bother about your system load when having small intervalls on scraping the
metrics.

### Setup Requirements

For running this extension you need to have at least TYPO3 v7. You should have access to a system scheduler like cron, 
to run the extbase commands for genearating the metrics output. You should also have a Prometheus setup, from which you
want to scrape the collected data.

### Beginning with prometheus

We recommend using composer to install and update the extension. After installing and activating the extension in the
TYPO3 Backend, please perform a database compare to create the prometheus table, in which all the output data is stored.

You also need to configure the IP Range in the extension configuration inside the extension manager to restrict the view 
on the metric data for 3rd parties. The default is to only allow access from localhost. You need at least to configure 
the IP address of your prometheus to have access to the metrics page.

The metrics path is `http://example.org/?eID=prometheus_metrics`.

## Reference


