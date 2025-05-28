<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>#FX Sitemap⁶</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style type="text/css">
					table{
						width: 100%;
						background: #f8f8f8;
					}
				
					body {
						font-family: arial;
						background: #444;
					}
					
					th {
						text-align:left;
						border-bottom: 1px dashed #444;
						padding: 10px 15px;
					}
					
					a {
						color:#444;
						text-decoration: none;
						border-bottom: 1px dotted;
					}
					
					td {
						color:#444;
						padding: 6px 15px;
						font-size:12px;
					}
					
					tr:hover td {background: #cdf;}
					
					h2{ 
						font-size: 25px; 
						color: #eee; 
						display: inline; 
						line-height: 1.7em;
					}
					
					h2 p{
						color: #fb9c37;
						display: inline;
					}
					sup{color: #ccc; font-size: 20px; margin: -5px}
				</style>
			</head>
			<body>
				<xsl:apply-templates></xsl:apply-templates>
			</body>
		</html>
	</xsl:template>
	
	
	<xsl:template match="sitemap:urlset">
        <div class="name">
            <h2>#FX <p>Sitemap</p></h2><sup>6 beta</sup> 
        </div>
		<div id="content">
			<table cellpadding="5">
				<tr>
					<th>loc</th>
					<th>lastmod</th>
					<th>priority</th>
					<th>changefreq</th>
				</tr>
				<xsl:for-each select="./sitemap:url">
					<tr>
						<td>
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>
							<a href="{$itemURL}">
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
						<td>
							<xsl:value-of select="sitemap:lastmod"/>
						</td>
						<td>
							<xsl:value-of select="sitemap:priority"/>
						</td>
						<td>
							<xsl:value-of select="sitemap:changefreq"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
	
	
	<xsl:template match="sitemap:sitemapindex">
        <div class="name">
            <h2>#FX <p>XML Sitemap</p></h2><sup>6 beta</sup> 
        </div>
		<div id="content">
			<table cellpadding="5">
				<tr>
					<th>loc</th>
				</tr>
				<xsl:for-each select="./sitemap:sitemap">
					<tr>
						<td>
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>
							<a href="{$itemURL}">
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
</xsl:stylesheet>