<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0">
  <xsl:output  method="text"/>
  <xsl:template match="/"><xsl:apply-templates /></xsl:template>
  <xsl:template match="//key">&lt;div  data-role="collapsible"&gt;&lt;h3&gt;<xsl:apply-templates />&lt;/h3&gt;</xsl:template>
  <xsl:template match="//string|//integer|//false">&lt;p&gt;<xsl:apply-templates />&lt;/p&gt;&lt;/div&gt;</xsl:template>
  <xsl:template match="//array">&lt;div&gt;<xsl:apply-templates />&lt;/div&gt;&lt;/div&gt;</xsl:template>
  <xsl:template match="//dict" >&lt;div data-role="collapsible-set"&gt;<xsl:apply-templates />&lt;/div&gt;</xsl:template>
  <xsl:template match="//array/string|//array/integer" >&lt;p &gt;<xsl:apply-templates />&lt;/p&gt;</xsl:template>
</xsl:stylesheet>