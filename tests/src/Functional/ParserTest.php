<?php

namespace Drupal\mathematical_formatter\Functional;

use Drupal\mathematical_formatter\Utility\Lexer\Lexer;
use Drupal\mathematical_formatter\Utility\Lexer\Parser;
use Drupal\mathematical_formatter\Utility\Lexer\Token;

use Drupal\Tests\UnitTestCase;

class ParserTest extends  UnitTestCase
{
    public function test_static_scan_algebra()
    {
        $parser = new Parser([' 2 +6 /3 -1 *6 ']);
        $parser->compute();

        $this->assertEquals(
            [ Lexer::T_NUMBER, Lexer::T_PLUS, Lexer::T_NUMBER, Lexer::T_DIV, Lexer::T_NUMBER, Lexer::T_MINUS, Lexer::T_NUMBER, Lexer::T_MUL, Lexer::T_NUMBER],
            array_map(function (Token $t) { return $t->getName(); }, $parser->getTokens())
        );

        $this->assertEquals(
            ['2', '+', '6', '/', '3', '-', '1', '*', '6'],
            array_map(function (Token $t) { return $t->getValue(); }, $parser->getTokens())
        );
    }

    /**
     * @dataProvider unableToParseProvider
     */
    public function test_validation_input_unable_parse($source)
    {
        $parser = new Parser();

        try {
            $parser->setInput([$source]);
            $parser->compute();
        }catch (\Exception $ex){
            $this->assertEquals('Unable to parse line '.$parser->getLexer()->getSource()[0], $ex->getMessage());
        }
    }

    /**
     * @dataProvider incorrectProvider
     */
    public function test_validation_incorrect_input($source)
    {
        $parser = new Parser();

        try {
            $parser->setInput([$source]);
            $parser->compute();
        }catch (\Exception $ex){
            $this->assertEquals('Incorrect input '.$parser->getLexer()->getSource()[0], $ex->getMessage());
        }
    }

    /**
     * @dataProvider divisionByZeroProvider
     */
    public function test_division_by_zero($source)
    {
        $parser = new Parser();
        try {
            $parser->setInput([$source]);
            $parser->compute();
        }catch (\Exception $ex){
            $this->assertEquals('Division by zero - incorrect input '.$parser->getLexer()->getSource()[0], $ex->getMessage());
        }
    }

    public function test_show_formula()
    {
        $parser = new Parser([' 2* 2 +12 /3 - 1 *6 /2 ']);
        $formula = $parser->showFormula();

        $this->assertEquals("2*2+12/3-1*6/2",$formula);
    }

    /**
     * @dataProvider provider
     */
    public function test_compute($source, $value)
    {
        $parser = new Parser([$source]);
        $compute = $parser->compute();
        $this->assertEquals($value, $compute);
    }

    public function provider()
    {
        return array(
            [' 2 +6 /3 -1 *6 ', -2],
            [' .2 +6 /3 -1 *6 ', -3.8],
            [' 2 +6 /0.2 -1 *6 ', 26],
            [' 2 +6 /3 -1 *6.2 ', -2.2],
            [' 2 +6 /3 -1 *0.2-0.5 ', 3.3],
            [' 2 +6 /3 -1 *.5-0.6 ', 2.9],
            [' 2 +6 /3 -1 *.5-0.6/0.8 ', 2.75],
            [' 2* 2 +12 /3 - 1 *6 ', 2],
            [' 2* 2 +12 /3 - 1 *6 /2 ', 5],
            [' 7* 2 +24 /3 - 1 *6 /2 +7 *2 - 6 /2 ', 2],
            [' 17* 2 +24 /3 - 1 *6 /2 +7 *2 - 6 /2 ', 22],
            [' 17* 2 +24 /3 - 1 *6 /2 +7 *2 - 6 /2 + 2 / 2', 21],
            [' 17* 2 +24 /3 - 1 *6 /2 +7 *2 - 6 /2 + 2 / 4', 21.5],
            [' 17* 2 +24 /3 - 1 *6 /2 +7 *2 - 6 /2 + 2 / 4 - 12 /6', 19.5],
        );
    }

    public function unableToParseProvider()
    {
        return array(
            [' 2. +6 /3 -1 *6 '],
            [' 2 +6 /3 -1 *6= '],
            [' 2 +6 /3 -1 *6! '],
            [' #2 +6 /3 -1 *6 '],
            [' ,2 +6 /3 -1 *6 '],
        );
    }

    public function incorrectProvider()
    {
        return array(
            [' 2 +6 /3 -1 *6- '],
            [' 2 +6 /3 -1 *'],
            [' +6 /3 -1 * 2'],
            [' 6 /3 -1 * 2 2'],
        );
    }

    public function divisionByZeroProvider()
    {
        return array(
            [' 2 +6 /3 -1 *6/0 '],
            [' 2 +6/0 /3 -1 *6 '],
            [' 2/0 +6 /3 -1 *6 '],
            [' 2 +6 /3 -1/0 *6 '],
        );
    }
}
