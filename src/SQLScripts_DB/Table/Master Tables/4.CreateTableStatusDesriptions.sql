USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'StatusDesriptions' )
 BEGIN
 
	create table StatusDesriptions	(
	SD_StatusID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	SD_TableName	VARCHAR(50),
	SD_ColumnName	VARCHAR(50),
	SD_ColumnValueINT	INT,
	SD_ColumnValueCHAR	VARCHAR(10),
	SD_StatusName	VARCHAR(50),
	SD_StatusDescription	VARCHAR(100),
	SD_CreatedBy	INT,
	SD_CreatedDate	DATETIME,
	SD_UpdatedBy	INT,
	SD_UpdatedDate	DATETIME 
	constraint Con_StatusDesriptions_CrBy_FK FOREIGN KEY ( SD_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_StatusDesriptions_UpdBy_FK FOREIGN KEY ( SD_UpdatedBy ) references UserDetails(UD_UserID)
	)


	CREATE NONCLUSTERED INDEX idx_StatusDesriptions_Table ON StatusDesriptions  (SD_TableName,SD_ColumnName)

 END

