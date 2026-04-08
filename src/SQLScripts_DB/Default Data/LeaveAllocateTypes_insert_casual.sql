USE [Allocate7]
GO

IF NOT EXISTS (SELECT 1 FROM [dbo].[LeaveAllocateTypes] WHERE Description='Casual')
       INSERT INTO [dbo].[LeaveAllocateTypes] 
           ([id]
           ,[Description]
           ,[AllocName]
           ,[ScheID]
           ,[isCreditable]
           ,[SortOrder]
           ,[ShowZero]
           ,[IncludeInReports]
           ,[CalcInReports]
           ,[HasCredits]
           ,[SelectiveHide])
          VALUES
           (9,'Casual','Casual',0,1,7,1,1,1,1,1)

GO
