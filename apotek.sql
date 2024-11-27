/*
SQLyog Professional v12.5.1 (64 bit)
MySQL - 10.4.28-MariaDB-log : Database - apotek
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`apotek` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `apotek`;

/*Table structure for table `tb_jenis_obat` */

DROP TABLE IF EXISTS `tb_jenis_obat`;

CREATE TABLE `tb_jenis_obat` (
  `Id_jenis` int(3) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(15) NOT NULL,
  `bentuk_obat` enum('Tablet','Sirup','kapsul') NOT NULL,
  PRIMARY KEY (`Id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_jenis_obat` */

insert  into `tb_jenis_obat`(`Id_jenis`,`nama_jenis`,`bentuk_obat`) values 
(1,'obat_bius','Sirup');

/*Table structure for table `tb_obat` */

DROP TABLE IF EXISTS `tb_obat`;

CREATE TABLE `tb_obat` (
  `Id_Obat` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_Obat` varchar(15) NOT NULL,
  `Stok_obat` int(5) NOT NULL,
  `Harga_satuan` int(8) NOT NULL,
  `Id_jenis` int(3) NOT NULL,
  PRIMARY KEY (`Id_Obat`),
  KEY `Id_jenis` (`Id_jenis`),
  CONSTRAINT `tb_obat_ibfk_1` FOREIGN KEY (`Id_jenis`) REFERENCES `tb_jenis_obat` (`Id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_obat` */

insert  into `tb_obat`(`Id_Obat`,`Nama_Obat`,`Stok_obat`,`Harga_satuan`,`Id_jenis`) values 
(1,'gtw',50,10000,1);

/*Table structure for table `tb_pegawai` */

DROP TABLE IF EXISTS `tb_pegawai`;

CREATE TABLE `tb_pegawai` (
  `Id_pegawai` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_pegawai` varchar(15) NOT NULL,
  `No_tlp` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `Nip` varchar(10) NOT NULL,
  `passwd` varchar(10) NOT NULL,
  PRIMARY KEY (`Id_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pegawai` */

insert  into `tb_pegawai`(`Id_pegawai`,`Nama_pegawai`,`No_tlp`,`email`,`Nip`,`passwd`) values 
(1,'metra','809-','xsxsxs','makarov','admin123');

/*Table structure for table `tb_pelanggan` */

DROP TABLE IF EXISTS `tb_pelanggan`;

CREATE TABLE `tb_pelanggan` (
  `Id_pelanggan` int(4) NOT NULL AUTO_INCREMENT,
  `Nama_pelanggan` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  `alamat` varchar(10) NOT NULL,
  `password` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pelanggan` */

insert  into `tb_pelanggan`(`Id_pelanggan`,`Nama_pelanggan`,`no_tlp`,`alamat`,`password`) values 
(1,'adam','d','d','user123'),
(2,'Metra Shandy','1234567','0','1qaz2wsx');

/*Table structure for table `tb_pembelian` */

DROP TABLE IF EXISTS `tb_pembelian`;

CREATE TABLE `tb_pembelian` (
  `Id_pembelian` int(5) NOT NULL AUTO_INCREMENT,
  `tanggal_pembelian` datetime NOT NULL,
  `tanggal_kadarluarsa` datetime NOT NULL,
  `jumlah_item` int(5) NOT NULL,
  `harga_satuan` int(10) NOT NULL,
  `Id_suplier` int(3) DEFAULT NULL,
  PRIMARY KEY (`Id_pembelian`),
  KEY `Id_suplier` (`Id_suplier`),
  CONSTRAINT `tb_pembelian_ibfk_1` FOREIGN KEY (`Id_suplier`) REFERENCES `tb_penjualan` (`Id_penjualan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian` */

/*Table structure for table `tb_pembelian_detail` */

DROP TABLE IF EXISTS `tb_pembelian_detail`;

CREATE TABLE `tb_pembelian_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_pembelian` int(5) DEFAULT NULL,
  `tanggal_kadarluarsa` datetime DEFAULT NULL,
  `jumlah_item` int(5) DEFAULT NULL,
  `harga satuan` int(10) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_pembelian` (`Id_pembelian`),
  CONSTRAINT `tb_pembelian_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_pembelian_detail_ibfk_2` FOREIGN KEY (`Id_pembelian`) REFERENCES `tb_pembelian` (`Id_pembelian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian_detail` */

/*Table structure for table `tb_penjualan` */

DROP TABLE IF EXISTS `tb_penjualan`;

CREATE TABLE `tb_penjualan` (
  `Id_penjualan` int(5) NOT NULL AUTO_INCREMENT,
  `Tanggal_penjualan` datetime NOT NULL,
  `jumlah_item` int(3) NOT NULL,
  `harga_total` int(8) NOT NULL,
  `Id_pelanggan` int(3) DEFAULT NULL,
  PRIMARY KEY (`Id_penjualan`),
  KEY `Id_pelanggan` (`Id_pelanggan`),
  CONSTRAINT `tb_penjualan_ibfk_1` FOREIGN KEY (`Id_pelanggan`) REFERENCES `tb_pelanggan` (`Id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan` */

/*Table structure for table `tb_penjualan_detail` */

DROP TABLE IF EXISTS `tb_penjualan_detail`;

CREATE TABLE `tb_penjualan_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_penjualan` int(5) DEFAULT NULL,
  `harga_satuan` int(8) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_penjualan` (`Id_penjualan`),
  CONSTRAINT `tb_penjualan_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_penjualan_detail_ibfk_2` FOREIGN KEY (`Id_penjualan`) REFERENCES `tb_penjualan` (`Id_penjualan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan_detail` */

/*Table structure for table `tb_suplier` */

DROP TABLE IF EXISTS `tb_suplier`;

CREATE TABLE `tb_suplier` (
  `Id_suplier` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_suplier` varchar(15) NOT NULL,
  `Alamat` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_suplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_suplier` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
