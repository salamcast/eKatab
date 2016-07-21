<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:c="urn:oasis:names:tc:opendocument:xmlns:container"
  xmlns:d="http://www.w3.org/2000/09/xmldsig#"
  version="1.0">
 <xsl:output method="text" />
 <xsl:template match="/">[encryption]
sig_val="<xsl:value-of select="//d:SignatureValue|//SignatureValue"/>"
key_val="<xsl:value-of select="//d:KeyName|//KeyName"/>"
<xsl:apply-templates select="//d:SignedInfo|//SignedInfo" />
 </xsl:template>
 <xsl:template match="//d:SignedInfo|//SignedInfo" >
can_meth="<xsl:value-of select="d:CanonicalizationMethod/@Algorithm|CanonicalizationMethod/@Algorithm"/>"
sig_meth="<xsl:value-of select="d:SignatureMethod/@Algorithm|SignatureMethod/@Algorithm"/>"
<xsl:apply-templates select="d:Reference" />
 </xsl:template> 
 <xsl:template match="d:Reference|Reference" >;<xsl:value-of select="@URI"/>
url[]="<xsl:value-of select="@URI"/>"
algorithm[]="<xsl:value-of select="d:DigestMethod/@Algorithm|DigestMethod/@Algorithm"/>"
digest[]="<xsl:value-of select="d:DigestValue|DigestValue"/>"
</xsl:template>
</xsl:stylesheet>