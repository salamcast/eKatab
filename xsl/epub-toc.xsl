<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:ncx="http://www.daisy.org/z3986/2005/ncx/" 
  version="1.0">
  <xsl:output  method="text"/>
  <xsl:param name="search" >section-0006.html</xsl:param>
  <xsl:template match="/"><xsl:apply-templates select="//ncx:navPoint|//navPoint" /></xsl:template>
  <xsl:template match="//ncx:navPoint|//navPoint">
    <xsl:if test="starts-with(ncx:content/@src|content/@src,$search)"><xsl:value-of select="ncx:navLabel/ncx:text|navLabel/text"/></xsl:if>
  </xsl:template>
</xsl:stylesheet>