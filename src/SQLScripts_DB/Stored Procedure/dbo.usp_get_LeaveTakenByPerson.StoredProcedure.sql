USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveTakenByPerson]    Script Date: 06/12/2025 18:20:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                      PROCEDURE [dbo].[usp_get_LeaveTakenByPerson]
	-- Add the parameters for the stored procedure here
	@scheduledpersonid varchar(100),
	@leaveyearstartdate varchar(100),
	@leaveyearenddate varchar(100)

AS
BEGIN

	SET NOCOUNT ON;
	DECLARE @ColumnList Varchar(Max)='',
			@vSQL NVARCHAR(Max),
			@SelectColumnList Varchar(Max)='';

		SELECT @ColumnList= STUFF((SELECT ',[' + AllocName + ']'
		  FROM LeaveAllocateTypes (nolock)
		   FOR XML PATH('')),1,1,'')

		SELECT @SelectColumnList = STUFF((SELECT ',SUM (LT.[' + AllocName +']) AS "' + AllocName + '"'
		  FROM LeaveAllocateTypes (nolock)
		   FOR XML PATH('')),1,1,'')

		SET @vSQL = 'SELECT LA.SchedulingPersonID,'+
							@SelectColumnList+'
					  FROM leaveapplications (nolock) LA
					 INNER JOIN UserDetails UD (nolock) ON UD.UD_UserID = LA.SchedulingPersonID
					  LEFT OUTER JOIN (
										 SELECT AllocName, 
												ApplicationID, 
												amount, 
												reasonid
										  FROM LeaveAllocateTypes LT (nolock)
										  LEFT JOIN ref_leaveapplications_amounts LP (nolock) ON LT.id = LP.LeaveTypeID
										) LA PIVOT ( MAX(Amount) FOR AllocName IN ('+@ColumnList+') ) LT
																ON LA.id = LT.applicationid
					WHERE ( LA.SchedulingPersonID = '+@scheduledpersonid+' )
					  AND ( LA.ddate >= CONVERT(DATETIME, '''+@leaveyearstartdate+''', 102) ) 
					  AND ( LA.ddate <= CONVERT(DATETIME, '''+@leaveyearenddate+''', 102))
					  AND ( LA.deleted = 0 )
					  AND ISNULL(LA.LeaveTypesID,0) > 0
					GROUP BY LA.SchedulingPersonID'
	
		exec sp_executesql @vSQL

END