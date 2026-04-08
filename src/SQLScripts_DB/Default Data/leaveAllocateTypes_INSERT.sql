USE [Allocate7]
GO

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
           (10,'Long Service','Long Service',0,1,8,1,1,1,1 ,1),(11,'Other','Other',0,1,9,1,1,1,1 ,1)
GO


