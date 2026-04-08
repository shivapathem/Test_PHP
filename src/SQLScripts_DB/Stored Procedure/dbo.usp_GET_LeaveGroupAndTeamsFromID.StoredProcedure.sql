USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_LeaveGroupAndTeamsFromID]    Script Date: 28/07/2025 15:51:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_GET_LeaveGroupAndTeamsFromID]  

@id VARCHAR(10)  
   
AS  
BEGIN  
 -- SET NOCOUNT ON added to prevent extra result sets from  
 -- interfering with SELECT statements.  
 SET NOCOUNT ON;  
 
 DECLARE    @query  AS NVARCHAR(MAX),
			@conditionString AS NVARCHAR(MAX),
			@conditionString2 AS NVARCHAR(MAX)

		 
	      SET @conditionString2 = 'ORDER BY LR.Description, LT.id'

    IF(@id! = 0)
	SET @conditionString = 'WHERE  LR.id = '+@id+' '
	ELSE 
	SET @conditionString = ''

		SET @query = 'SELECT LR.ID, LR.Description, LT.LeaveStarts, LT.SNLeaveStarts, LT.LeaveEnds, 
		LR.HoursPerLeaveDay, LR.ShowLeaveOverLimit, LR.ExtraLeaveClicks, 
		LR.SummerLeaveOverLimit, LR.email, LR.emailcopiesto, LT.ID AS TypeID, 
		LT.description AS TypeDescription, LT.dependant, LT.countclicks, LT.defaultamounts
		FROM LeaveRequestGroups (nolock) LR LEFT OUTER JOIN
		leave_types (nolock) LT ON LR.ID = LT.GroupID '+ @conditionString +''+ @conditionString2 + ''

exec sp_executesql @query
END
