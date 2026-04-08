USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'DivisionID'
          AND Object_ID = Object_ID(N'[dbo].[REF_MasterDutyColours]'))
BEGIN
    Alter Table [dbo].[REF_MasterDutyColours] ADD DivisionID int;


END


GO








