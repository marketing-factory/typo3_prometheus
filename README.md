# TYPO3 Prometheus Metrics Extension

This extension provides a Prometheus metrics endpoint for TYPO3 instances. It exposes various TYPO3-specific metrics that can be scraped by Prometheus to monitor the health and performance of your TYPO3 site.

## Installation

1. Install the extension via composer:
   ```
   composer require mfd/typo3-prometheus
   ```

2. Activate the extension in the Extension Manager or via command line:
   ```
   vendor/bin/typo3 extension:activate prometheus
   ```

## Usage

After installation, a metrics endpoint will be available at `/metrics`. This endpoint will return Prometheus-compatible metrics in the OpenMetrics text format.

## Available Metrics

The following metrics are currently exposed:

- `typo3_version_info`: Information about the TYPO3 version and application context
- `typo3_memory_usage_bytes`: Current memory usage in bytes
- `typo3_memory_peak_usage_bytes`: Peak memory usage in bytes
- `typo3_pages_total`: Total number of pages in the system
- `typo3_content_elements_total`: Total number of content elements

## Configuration

No additional configuration is needed. The extension works out of the box.

## Security Considerations

It's recommended to restrict access to the `/metrics` endpoint in production environments, as it may expose sensitive information. You can use your web server's configuration to restrict access based on IP addresses or authentication.

## License

This extension is licensed under the terms of the GNU General Public License version 3 or later.
