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
  `bentuk_obat` enum('Tablet','Sirup','kapsul','bubuk','makanan') NOT NULL,
  PRIMARY KEY (`Id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_jenis_obat` */

insert  into `tb_jenis_obat`(`Id_jenis`,`nama_jenis`,`bentuk_obat`) values 
(1,'Obat','Sirup'),
(2,'Obat','Tablet'),
(3,'Obat','kapsul'),
(4,'Suplemen','Tablet'),
(5,'Suplemen','kapsul'),
(6,'Vitamin','Tablet'),
(7,'Vitamin','kapsul'),
(8,'Vitamin','Sirup'),
(9,'Produk Bayi','bubuk'),
(10,'Produk Bayi','makanan');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_obat` */

insert  into `tb_obat`(`Id_Obat`,`Nama_Obat`,`Stok_obat`,`Harga_satuan`,`Id_jenis`) values 
(1,'gtw',-40,10000,1),
(2,'ya',142,799,2),
(6,'mugi',119,1256,8),
(7,'bau',40,1256,4),
(8,'paracetamol',48,10000,2),
(9,'paramex',39,10000,4);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pegawai` */

insert  into `tb_pegawai`(`Id_pegawai`,`Nama_pegawai`,`No_tlp`,`email`,`Nip`,`passwd`) values 
(1,'metra','809ffff','edd@gmail.com','2305551110','admin123');

/*Table structure for table `tb_pelanggan` */

DROP TABLE IF EXISTS `tb_pelanggan`;

CREATE TABLE `tb_pelanggan` (
  `Id_pelanggan` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  `alamat` varchar(10) NOT NULL,
  `password` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pelanggan` */

insert  into `tb_pelanggan`(`Id_pelanggan`,`username`,`email`,`no_tlp`,`alamat`,`password`) values 
(1,'anonim','-','-','-','user123'),
(2,'Metra Shandy','','1234567','0','1qaz2wsx'),
(3,'1qaz','','872927278710','jepang','$2y$10$ddTyeB4p'),
(4,'kin','','098765432','jawa','$2y$10$qoiiP23V');

/*Table structure for table `tb_pembelian` */

DROP TABLE IF EXISTS `tb_pembelian`;

CREATE TABLE `tb_pembelian` (
  `Id_pembelian` int(5) NOT NULL AUTO_INCREMENT,
  `tanggal_pembelian` date NOT NULL,
  `total_item` int(5) NOT NULL,
  `total_harga` int(10) NOT NULL,
  `Total_bayar` int(8) NOT NULL,
  `kembalian` int(8) NOT NULL,
  `Id_suplier` int(3) NOT NULL,
  PRIMARY KEY (`Id_pembelian`),
  KEY `Id_suplier` (`Id_suplier`),
  CONSTRAINT `tb_pembelian_ibfk_2` FOREIGN KEY (`Id_suplier`) REFERENCES `tb_suplier` (`Id_suplier`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian` */

insert  into `tb_pembelian`(`Id_pembelian`,`tanggal_pembelian`,`total_item`,`total_harga`,`Total_bayar`,`kembalian`,`Id_suplier`) values 
(7,'2024-12-25',40,400000,500000,100000,1);

/*Table structure for table `tb_pembelian_detail` */

DROP TABLE IF EXISTS `tb_pembelian_detail`;

CREATE TABLE `tb_pembelian_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_pembelian` int(5) DEFAULT NULL,
  `tanggal_kadarluarsa` date DEFAULT NULL,
  `jumlah_item` int(5) DEFAULT NULL,
  `harga_satuan` int(10) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_pembelian` (`Id_pembelian`),
  CONSTRAINT `tb_pembelian_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_pembelian_detail_ibfk_2` FOREIGN KEY (`Id_pembelian`) REFERENCES `tb_pembelian` (`Id_pembelian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian_detail` */

insert  into `tb_pembelian_detail`(`Id_obat`,`Id_pembelian`,`tanggal_kadarluarsa`,`jumlah_item`,`harga_satuan`) values 
(8,7,'0000-00-00',40,10000);

/*Table structure for table `tb_penjualan` */

DROP TABLE IF EXISTS `tb_penjualan`;

CREATE TABLE `tb_penjualan` (
  `Id_penjualan` int(5) NOT NULL AUTO_INCREMENT,
  `Tanggal_penjualan` date NOT NULL,
  `Total_item` int(3) NOT NULL,
  `harga_total` int(8) NOT NULL,
  `biaya_kirim` int(8) NOT NULL,
  `total_biaya` int(8) NOT NULL,
  `Total_bayar` int(8) NOT NULL,
  `Kembalian` int(8) NOT NULL,
  `Id_pelanggan` int(3) NOT NULL,
  PRIMARY KEY (`Id_penjualan`),
  KEY `Id_pelanggan` (`Id_pelanggan`),
  CONSTRAINT `tb_penjualan_ibfk_1` FOREIGN KEY (`Id_pelanggan`) REFERENCES `tb_pelanggan` (`Id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan` */

insert  into `tb_penjualan`(`Id_penjualan`,`Tanggal_penjualan`,`Total_item`,`harga_total`,`biaya_kirim`,`total_biaya`,`Total_bayar`,`Kembalian`,`Id_pelanggan`) values 
(1,'2024-12-01',40,215980,0,0,100000000,99784020,1),
(11,'2024-12-25',2,11256,0,0,20000,8744,1);

/*Table structure for table `tb_penjualan_detail` */

DROP TABLE IF EXISTS `tb_penjualan_detail`;

CREATE TABLE `tb_penjualan_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_penjualan` int(5) DEFAULT NULL,
  `jumlah_item` int(5) DEFAULT NULL,
  `harga_satuan` int(8) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_penjualan` (`Id_penjualan`),
  CONSTRAINT `tb_penjualan_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_penjualan_detail_ibfk_2` FOREIGN KEY (`Id_penjualan`) REFERENCES `tb_penjualan` (`Id_penjualan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan_detail` */

insert  into `tb_penjualan_detail`(`Id_obat`,`Id_penjualan`,`jumlah_item`,`harga_satuan`) values 
(6,11,1,1256),
(8,11,1,10000),
(2,1,20,799),
(1,1,20,10000);

/*Table structure for table `tb_pesanan` */

DROP TABLE IF EXISTS `tb_pesanan`;

CREATE TABLE `tb_pesanan` (
  `Id_pesanan` varchar(50) NOT NULL,
  `tanggal_pemesanan` date NOT NULL,
  `Total_item` int(8) NOT NULL,
  `Harga_total` int(8) NOT NULL,
  `Id_pelanggan` int(4) NOT NULL,
  `status` enum('DIBATALKAN','PENDING','SELESAI') NOT NULL,
  PRIMARY KEY (`Id_pesanan`),
  KEY `Id_pelanggan` (`Id_pelanggan`),
  CONSTRAINT `tb_pesanan_ibfk_1` FOREIGN KEY (`Id_pelanggan`) REFERENCES `tb_pelanggan` (`Id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pesanan` */

insert  into `tb_pesanan`(`Id_pesanan`,`tanggal_pemesanan`,`Total_item`,`Harga_total`,`Id_pelanggan`,`status`) values 
('3-1733625609','2024-12-08',1,10000,3,'SELESAI'),
('3-1733625628','2024-12-08',1,799,3,'SELESAI'),
('4-1734935260','2024-12-23',8,70799,4,'SELESAI'),
('4-1734937136','2024-12-23',10,100000,4,'SELESAI');

/*Table structure for table `tb_pesanan_detail` */

DROP TABLE IF EXISTS `tb_pesanan_detail`;

CREATE TABLE `tb_pesanan_detail` (
  `Id_pesanan` varchar(50) NOT NULL,
  `Id_obat` int(4) NOT NULL,
  `jumlah_item` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_pesanan` (`Id_pesanan`),
  CONSTRAINT `tb_pesanan_detail_ibfk_2` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_pesanan_detail_ibfk_3` FOREIGN KEY (`Id_pesanan`) REFERENCES `tb_pesanan` (`Id_pesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pesanan_detail` */

insert  into `tb_pesanan_detail`(`Id_pesanan`,`Id_obat`,`jumlah_item`,`harga_satuan`) values 
('3-1733625609',1,1,10000),
('3-1733625628',2,1,799),
('4-1734935260',1,7,10000),
('4-1734935260',2,1,799),
('4-1734937136',1,10,10000);

/*Table structure for table `tb_suplier` */

DROP TABLE IF EXISTS `tb_suplier`;

CREATE TABLE `tb_suplier` (
  `Id_suplier` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_suplier` varchar(15) NOT NULL,
  `Alamat` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_suplier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_suplier` */

insert  into `tb_suplier`(`Id_suplier`,`Nama_suplier`,`Alamat`,`email`,`no_tlp`) values 
(1,'kimia farma','denpasar','kimi@gmail.com','0987523145'),
(2,'farmasi','denpasar','far@gmail.com','098654');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
