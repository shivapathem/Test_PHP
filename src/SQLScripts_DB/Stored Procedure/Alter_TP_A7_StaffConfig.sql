USE [AllocateLink]

GO

 
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'IsTermTime'
          AND Object_ID = Object_ID(N'[dbo].[TP_A7_StaffConfig]'))
BEGIN
ALTER TABLE TP_A7_StaffConfig add IsTermTime bit
END
 

GO