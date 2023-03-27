-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 21, 2019 at 01:53 PM
-- Server version: 10.2.17-MariaDB
-- PHP Version: 7.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u344052086_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `userFreeForm`
--

CREATE TABLE `userFreeForm` (
  `IDX` int(11) NOT NULL COMMENT 'Primary key index',
  `username` text NOT NULL COMMENT 'hidden in form, populated by POST',
  `jurisdiction` text NOT NULL COMMENT 'examples: Federal, Wisconsin, Waukesha County, City of Waukesha, Waukesha district 13',
  `category` int(11) NOT NULL COMMENT 'examples: education, energy, law enforcement, immigration, fiscal policy',
  `issue` text NOT NULL COMMENT 'The issue, entered by the user',
  `platform` text NOT NULL COMMENT 'This is the user''s stance',
  `importance` int(11) NOT NULL COMMENT 'How important is this issue to the user? [0,100]',
  `informed` int(11) NOT NULL COMMENT 'How informed does the user consider themselves on this issue? [0,100]',
  `comment` text NOT NULL,
  `ref` text NOT NULL COMMENT 'references (links, publications, etc)'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='used by bryanhermsen.com/townhall/myPlatform/';

--
-- Dumping data for table `userFreeForm`
--

INSERT INTO `userFreeForm` (`IDX`, `username`, `jurisdiction`, `category`, `issue`, `platform`, `importance`, `informed`, `comment`, `ref`) VALUES
(2, 'bhermsen', 'Federal', 0, 'Originalism - attempting to discern what the people who adopted a constitutional provision understood it to mean', 'In every era, courts should attempt to interpret the Constitution and any law the way the original author intended, to the best of their ability.', 80, 90, '', 'Milwaukee Journal, Jan-1-2016'),
(5, 'bhermsen', 'Federal', 0, 'Defining the beginning of human life', 'A human person should receive protection under the law and be treated with the dignity due a human person when he or she, as an egg, attaches to the Uterine Wall.', 95, 75, '', ''),
(6, 'bhermsen', 'Wisconsin', 0, 'Responsibilities of fatherhood', 'When pregnancy occurs, the father should bear responsibility for the physical safety and needs of the children the extent of his ability, including acting as primary financial provider. Whereas abortion is legal, when performed, the father should bear the complete cost: not the mother nor the state.', 98, 85, 'The father must be held responsible. Whereas the mother bears the responsibility of physically carrying the child, the father should bear whatever responsibility he is able.', ''),
(7, 'bhermsen', 'Federal', 0, 'Banning travel from known terrorist states', 'The Presidents recent ban on travel by persons from seven foreign states is not morally justified. It puts undue burden on persons wishing to enter the country for purposes that would be mutually beneficial to themselves and these United States', 60, 35, 'This executive order seems rash', ''),
(8, 'bhermsen', 'Federal', 0, 'School Choice', 'I support school choice because it affords students from poor families the opportunity to attend better schools than the one in their district and promotes competition between schools. I believe it will lead kids to compete on merit to get in the best schools, instead of it being determined by the location of their family\'s home.', 65, 40, 'There is a legitimate concern with school Choice that it may cause inequality among schools, as the best students go to the best schools and the struggling students are gathered together in the worst schools. That is a danger. But we already have significant inequality in the public school system we have today! Worse yet, today it is based on where families live, so the only ones able to attend the best schools are the ones whose families can afford to live in the best districts. That is the worst way to segregate, because it perpetuates poverty to the next generation. The school Choice system provides vouchers on a need basis, and it prohibits schools from charging more than the value of the voucher.', 'NPR podcast about Betsy DeVos, January or February 2017'),
(9, 'bhermsen', 'Federal', 0, 'Border Adjustment Tax', 'I believe the US should charge a Border Adjustment Tax on foreign imports', 65, 40, '', 'https://www.surveymonkey.com/r/7F9B7F3'),
(10, 'bhermsen', 'Federal ', 0, 'Trade with Asia', 'It is important for The United States to have strong trade agreements with allies in the Pacific to promote collaboration between our societies.', 50, 25, 'Trade with Pacific allies, such as the TPP, is important to help America become more of a leader in that region of the world. I don\'t know the ', 'Based on NPR podcast about globalism in the Trump era, Feb 2017'),
(11, 'bhermsen', 'Federal', 0, 'Clean Power Plan', 'Republicans should replace the Clean Power Plan with an order-adjustable carbon tax that returns the revenue to taxpayers.', 80, 20, 'Interesting idea, worth more investigation ', 'This platform is a direct quote from Alex Bozmoski, \"A better way on climate\", MJS, 2/19/17'),
(12, 'bhermsen', 'Federal', 0, 'Carbon tax', 'I support a border-adjustable carbon tax', 80, 20, '', ''),
(13, 'bhermsen', 'State', 0, 'Carbon tax', 'I support a border-adjustable carbon tax. In the absence of federal legislation, I support a state carbon tax that is border adjustable for exports from Wisconsin', 80, 16, '', ''),
(14, 'bhermsen', 'Federal', 0, 'Nomination of Supreme Court Justices', 'Senator McConnell should not have \"gone nuclear\" in the nomination of Neil Gorsuch. He should have allowed a vote under the existing rules.', 50, 30, '', ''),
(15, 'bhermsen', 'Wisconsin', 0, 'Allowing criminals to carry', 'Tighter restrictions should be in place to stop criminals and drug traffickers to conceal and carry', 80, 50, '', 'https://twitter.com/i/redirect?url=https%3A%2F%2Ftwitter.com%2Fnowthisnews%2Fstatus%2F880464645806374912%3Ft%3D1%26cn%3DZmxleGlibGVfcmVjc18y%26iid%3Ddd01b2ad74844d988afff03866a9c7cb%26uid%3D71449770%26nid%3D244%2B272699400&t=1&cn=ZmxleGlibGVfcmVjc18y&sig=9ebf5fca613fb12d09a7e6ff51afcbd9918c72dd&iid=dd01b2ad74844d988afff03866a9c7cb&uid=71449770&nid=244+272699400'),
(16, 'bhermsen', 'Federal', 0, 'Biggest threats to the American republic ', 'The biggest threats to America are not terrorism, North Korea, or Iran. They are as follows:\r\n - corruption in Congress \r\n - executive overreach \r\n - economic inequality \r\n - political polarization \r\n - the national debt\r\n - lack of a national mission (manifest destiny, challenge from a foreign power, etc)', 90, 55, '', 'Fall of the Roman Republic (Art of Manliness podcast)'),
(17, 'bhermsen', 'Federal', 0, 'Another threat', '- political indifference of the masses. If they are indifferent or not Latin attention, they can be manipulated.', 90, 50, '', ''),
(18, 'bhermsen', 'Federal ', 0, 'Biggest threats to the country ', 'Include Executive over-reach ', 90, 50, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `userFreeForm`
--
ALTER TABLE `userFreeForm`
  ADD PRIMARY KEY (`IDX`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `userFreeForm`
--
ALTER TABLE `userFreeForm`
  MODIFY `IDX` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key index', AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
