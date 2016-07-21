<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:o="urn:oasis:names:tc:opendocument:xmlns:container"
  version="1.0">
  <xsl:output  method="text"/>
  <xsl:template match="/"><xsl:value-of select="/container/rootfiles/rootfile/@full-path|/o:container/o:rootfiles/o:rootfile/@full-path"/></xsl:template>
</xsl:stylesheet>