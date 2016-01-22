--
-- Tabelstructuur voor tabel `whatpulse_data`
--

CREATE TABLE IF NOT EXISTS `whatpulse_data` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `Username` varchar(50) NOT NULL,
  `Team` varchar(20) NOT NULL,
  `UserID` int(8) NOT NULL,
  `Pulses` int(10) NOT NULL,
  `Keys1` int(15) NOT NULL,
  `Clicks` int(15) NOT NULL,
  `DownloadMB` varchar(15) NOT NULL,
  `UploadMB` varchar(15) NOT NULL,
  `Download` varchar(15) NOT NULL,
  `Upload` varchar(15) NOT NULL,
  `UptimeSeconds` int(18) NOT NULL,
  `UptimeShort` varchar(30) NOT NULL,
  `UptimeLong` varchar(100) NOT NULL,
  `Rank_Keys` int(10) NOT NULL,
  `Rank_Clicks` int(10) NOT NULL,
  `Rank_Download` int(10) NOT NULL,
  `Rank_Upload` int(10) NOT NULL,
  `Rank_Uptime` int(10) NOT NULL,
  `LastPulse` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;