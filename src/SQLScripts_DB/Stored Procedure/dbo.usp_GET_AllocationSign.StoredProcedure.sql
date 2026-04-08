USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_AllocationSign]    Script Date: 16/07/2022 12:18:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_GET_AllocationSign]  
 -- Add the parameters for the stored procedure here  
@dutyId  int,
@scheduledPersonId INT,
@teamId INT,
@screenType int  
   
AS  
BEGIN  
 -- SET NOCOUNT ON added to prevent extra result sets from  
 -- interfering with SELECT statements.  
 SET NOCOUNT ON;  
 
 IF(@screenType = 0) 
	BEGIN
		SELECT ID, StartTime, EndTime, DutyName FROM Allocations WHERE ID = @dutyId
	END
ELSE
	BEGIN
		SELECT ID, StartTime, EndTime, DutyName FROM Allocations_Publish WHERE ID = @dutyId
	END

		
END  
