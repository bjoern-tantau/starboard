<?php

class Models_XmlHelperTest extends TestCase
{

    /**
     * Are xml Elements merged correctly?
     *
     * @test
     */
    public function testMergeOneXml()
    {
        $xml = '<root>'
            . '<element bla="bla">'
            . '<foo>foo</foo>'
            . '</element>'
            . '<element bla="blub">'
            . '<bar>bar</bar>'
            . '</element>'
            . '<element example="true">'
            . '<bar>foobar</bar>'
            . '</element>'
            . '<element example="true">'
            . '<fourth>fourth</fourth>'
            . '</element>'
            . '</root>';
        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root>'
            . '<element bla="blub" example="true">'
            . '<foo>foo</foo>'
            . '<bar>foobar</bar>'
            . '<fourth>fourth</fourth>'
            . '</element>'
            . '</root>'
            . "\n";

        $simpleXml = simplexml_load_string($xml);
        $actual = XmlHelper::merge($simpleXml)->asXML();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Are xml Elements merged correctly?
     *
     * @test
     */
    public function testMergeTwoXmls()
    {
        $xml1 = '<root>'
            . '<element bla="bla">'
            . '<foo>foo</foo>'
            . '</element>'
            . '</root>';
        $xml2 = '<root>'
            . '<element bla="blub">'
            . '<bar>bar</bar>'
            . '</element>'
            . '<element example="true">'
            . '<bar>foobar</bar>'
            . '</element>'
            . '</root>';
        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root>'
            . '<element bla="blub" example="true">'
            . '<foo>foo</foo>'
            . '<bar>foobar</bar>'
            . '</element>'
            . '</root>'
            . "\n";

        $simpleXml1 = simplexml_load_string($xml1);
        $simpleXml2 = simplexml_load_string($xml2);
        $actual = XmlHelper::merge($simpleXml1, $simpleXml2)->asXML();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Are xml Elements merged correctly?
     *
     * @test
     */
    public function testMergeThreeXmls()
    {
        $xml1 = '<root>'
            . '<element bla="bla">'
            . '<foo>foo</foo>'
            . '</element>'
            . '</root>';
        $xml2 = '<root>'
            . '<element bla="blub">'
            . '<bar>bar</bar>'
            . '</element>'
            . '</root>';
        $xml3 = '<root>'
            . '<element example="true">'
            . '<bar>foobar</bar>'
            . '</element>'
            . '</root>';
        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root>'
            . '<element bla="blub" example="true">'
            . '<foo>foo</foo>'
            . '<bar>foobar</bar>'
            . '</element>'
            . '</root>'
            . "\n";

        $simpleXml1 = simplexml_load_string($xml1);
        $simpleXml2 = simplexml_load_string($xml2);
        $simpleXml3 = simplexml_load_string($xml3);
        $actual = XmlHelper::merge($simpleXml1, $simpleXml2, $simpleXml3)->asXML();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Are xml Elements merged correctly?
     *
     * @test
     */
    public function testMergeFourXmls()
    {
        $xml1 = '<root>'
            . '<element bla="bla">'
            . '<foo>foo</foo>'
            . '</element>'
            . '</root>';
        $xml2 = '<root>'
            . '<element bla="blub">'
            . '<bar>bar</bar>'
            . '</element>'
            . '</root>';
        $xml3 = '<root>'
            . '<element example="true">'
            . '<bar>foobar</bar>'
            . '</element>'
            . '</root>';
        $xml4 = '<root>'
            . '<element example="true">'
            . '<fourth>fourth</fourth>'
            . '</element>'
            . '</root>';
        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root>'
            . '<element bla="blub" example="true">'
            . '<foo>foo</foo>'
            . '<bar>foobar</bar>'
            . '<fourth>fourth</fourth>'
            . '</element>'
            . '</root>'
            . "\n";

        $simpleXml1 = simplexml_load_string($xml1);
        $simpleXml2 = simplexml_load_string($xml2);
        $simpleXml3 = simplexml_load_string($xml3);
        $simpleXml4 = simplexml_load_string($xml4);
        $actual = XmlHelper::merge($simpleXml1, $simpleXml2, $simpleXml3, $simpleXml4)->asXML();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test if nodes can be deleted correctly.
     *
     * @test
     */
    public function testDeleteNode()
    {
        $xml = '<root><foo>foo</foo><bar>bar</bar></root>';
        $simpleXml = simplexml_load_string($xml);

        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root><bar>bar</bar></root>'
            . "\n";

        XmlHelper::deleteNode($simpleXml->foo);
        $actual = $simpleXml->asXml();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test if nodes can be appended correctly.
     *
     * @test
     */
    public function testAppendChild()
    {
        $xml = '<root><foo>foo</foo></root>';
        $simpleXml = simplexml_load_string($xml);
        $xml2 = '<bar>bar</bar>';
        $simpleXml2 = simplexml_load_string($xml2);

        $expected = '<?xml version="1.0"?>' . "\n"
            . '<root><foo>foo</foo><bar>bar</bar></root>'
            . "\n";

        XmlHelper::appendChild($simpleXml, $simpleXml2);
        $actual = $simpleXml->asXml();

        $this->assertEquals($expected, $actual);
    }

}
