USE [AllocateLink]
GO

/****** Object:  Table [dbo].[RequestTypes]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'PaymentTypeShortCode'
          AND Object_ID = Object_ID(N'[dbo].[TP_A7_REF_PaymentType]'))
BEGIN
ALTER TABLE [dbo].[TP_A7_REF_PaymentType] ADD PaymentTypeShortCode varchar(3)
END

GO