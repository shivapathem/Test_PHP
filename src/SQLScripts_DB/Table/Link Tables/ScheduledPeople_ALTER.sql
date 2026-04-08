USE [Allocate7]
GO
/****** Object:  Table [dbo].[ScheduledPeople]    Script Date: 09/11/2021 10:57:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsAdditionalLeave' AND Object_ID = Object_ID(N'[dbo].[ScheduledPeople]'))
BEGIN
    ALTER TABLE dbo.ScheduledPeople ADD IsAdditionalLeave BIT NOT NULL DEFAULT(0)  ;
END
 
GO
 
IF  EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsAdditionalLeave' AND Object_ID = Object_ID(N'[dbo].[ScheduledPeople]'))
BEGIN
    UPDATE ScheduledPeople SET IsAdditionalLeave = 0;
END


GO